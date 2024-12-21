<?php

namespace App\Http\Controllers;

use App\Models\Community;
use Illuminate\Support\Facades\Auth;

use Illuminate\Http\Request;
use App\Models\Post;
use Illuminate\Support\Facades\Cache;
use App\Models\Vote;
use App\Models\Topic;
use App\Models\News;

use App\Enums\TopicStatus;
use App\Models\PostVote;
use IcehouseVentures\LaravelChartjs\Facades\Chartjs;
use Illuminate\Support\Facades\DB;

class FeedController extends Controller
{
  private function fetchPostData($query)
    {
        $posts = $query->withCount([
            'votes as upvotes_count' => fn($q) => $q->where('upvote', true),
            'votes as downvotes_count' => fn($q) => $q->where('upvote', false),
            'comments as comments_count'
        ])->get();
    
        foreach ($posts as $post) {
            $post->user_upvoted = Auth::check() ? $post->userVote(Auth::user()->id)?->upvote ?? false : false;
            $post->user_downvoted = Auth::check() ? !$post->userVote(Auth::user()->id)?->upvote ?? false : false;
        }
    
        return $posts;
    }

  // Fetch all posts from user's communities, order them by vote quantity, then by creation date
  public function home()
  {

    $cachedPosts = Cache::get('user_posts');

    if ($cachedPosts) {
      return response()->json($cachedPosts);
    }

    $authUser = Auth::user();


    $posts = Post::withCount('votes')
      ->whereIn('community_id', $authUser->communities->pluck('id'))
      ->where(function ($query) {
        $query->whereDoesntHave('topic') 
              ->orWhereHas('topic', function ($subQuery) {
                  $subQuery->where('status', TopicStatus::Accepted->value); 
              });
    })
      ->orderBy('votes_count', 'desc')
      ->orderBy('creation_date', 'desc')
      ->get();

    $news = $posts->filter(function ($post) {
      return !is_null($post->news); 
  })->sortByDesc(function ($post) {
      return [$post->score, $post->creation_date];
  });

  $topics = $posts->filter(function ($post) {
      return !is_null($post->topic); 
  })->sortByDesc(function ($post) {
      return [$post->score, $post->creation_date]; 
  });

    return view('pages.home', [
      'news' => $news,
      'topics' => $topics
    ]);
  }


  // Fetch posts from public communities created within the last 72 hours, ordered by vote quantity, then by creation date
  public function global()
  {
    $posts = Post::withCount('votes')
    ->whereHas('community', function ($query) {
        $query->where('privacy', false);
    })
    ->where('creation_date', '>', now()->subHours(72))
    ->where(function ($query) {
        $query->whereDoesntHave('topic') 
              ->orWhereHas('topic', function ($subQuery) {
                  $subQuery->where('status', TopicStatus::Accepted->value);
              });
    })
    ->orderBy('votes_count', 'desc')
    ->orderBy('creation_date', 'desc')
    ->get();


    $news = $posts->filter(function ($post) {
      return !is_null($post->news); 
  })->sortByDesc(function ($post) {
      return [$post->score, $post->creation_date]; 
  });


  $topics = $posts->filter(function ($post) {
      return !is_null($post->topic);
  })->sortByDesc(function ($post) {
      return [$post->score, $post->creation_date]; 
  });

    return view('pages.global', [
      'news' => $news,
      'topics' => $topics
    ]);
  }


  // Fetch posts from user's communities created in the last 72 hours, order them by creation date
  public function recent()
  {

    $authUser = Auth::user(); 


    $posts = Post::withCount('votes')
      ->whereIn('community_id', $authUser->communities->pluck('id'))
      ->where('creation_date', '>', now()->subHours(72))
      ->where(function ($query) {
        $query->whereDoesntHave('topic') 
              ->orWhereHas('topic', function ($subQuery) {
                  $subQuery->where('status', TopicStatus::Accepted->value); 
              });
    })
      ->orderBy('creation_date', 'desc')
      ->get();


  $news = $posts->filter(function ($post) {
      return !is_null($post->news); 
  })->sortByDesc(function ($post) {
      return [ $post->creation_date]; 
  });

  $topics = $posts->filter(function ($post) {
      return !is_null($post->topic); 
  })->sortByDesc(function ($post) {
      return [ $post->creation_date];
  });

    return view('pages.recent', [
      'news' => $news,
      'topics' => $topics
    ]);
  }

  public function aboutUs()
  {
    return view('pages.about_us');
  }

  public function bestof()
  {
      $topTopics = Topic::select('topics.*')
          ->addSelect([
              'votes_count' => function ($query) {
                  $query->selectRaw('COUNT(*)')
                      ->from('votes')
                      ->join('post_votes', 'post_votes.vote_id', '=', 'votes.id')
                      ->whereColumn('topics.post_id', 'post_votes.post_id');
              }
          ])
          ->orderBy('votes_count', 'desc')
          ->limit(10)
          ->get();
  
      $topNews = News::select('news.*')
          ->addSelect([
              'votes_count' => function ($query) {
                  $query->selectRaw('COUNT(*)')
                      ->from('votes')
                      ->join('post_votes', 'post_votes.vote_id', '=', 'votes.id')
                      ->whereColumn('news.post_id', 'post_votes.post_id');
              }
          ])
          ->orderBy('votes_count', 'desc')
          ->limit(10)
          ->get();
  
      
      return view('pages.bestof', [
          'topTopics' => $topTopics,
          'topNews' => $topNews,
      ]);
  }


  private function newCommunitiesChart()
  {
    // Get the date range for the last 14 days
    $startDate = now()->subDays(13)->startOfDay();
    $endDate = now()->endOfDay();

    // Fetch the data
    $data = DB::table('communities')
      ->select(DB::raw('DATE(creation_date) as date_created'), DB::raw('COUNT(*) as new_communities'))
      ->whereBetween('creation_date', [$startDate, $endDate])
      ->groupBy('date_created')
      ->orderBy('date_created', 'asc')
      ->get();

    // Generate a list of dates for the last 14 days
    $labels = collect();
    for ($date = $startDate->copy(); $date <= $endDate; $date->addDay()) {
      $labels->push($date->toDateString());
    }

    // Map the counts to the corresponding dates
    $counts = $labels->map(function ($date) use ($data) {
      return $data->firstWhere('date_created', $date)?->new_communities ?? 0;
    });

    // Build the chart
    $chart = Chartjs::build()
      ->name('newCommunitiesChart')
      ->type('line')
      ->size(['width' => 400, 'height' => 200])
      ->labels($labels->toArray())
      ->datasets([
        [
          "label" => "New Communities",
          "backgroundColor" => "rgba(54, 162, 235, 0.2)",
          "borderColor" => "rgba(54, 162, 235, 1)",
          "pointBorderColor" => "rgba(54, 162, 235, 1)",
          "pointBackgroundColor" => "rgba(54, 162, 235, 1)",
          "pointHoverBackgroundColor" => "#fff",
          "pointHoverBorderColor" => "rgba(54, 162, 235, 1)",
          "data" => $counts->toArray(),
          "fill" => false,
        ]
      ])
      ->options([
        "scales" => [
          "y" => [
            "beginAtZero" => true,
            "ticks" => [
              "stepSize" => 1, // Ensures only integers are displayed on the y-axis
            ],
          ],
          "x" => [
            "type" => "time",
            "time" => [
              "unit" => "day",
            ],
          ],
        ],
      ]);

    return $chart;
  }
}
