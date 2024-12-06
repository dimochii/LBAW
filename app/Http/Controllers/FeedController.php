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

use App\Models\PostVote;
use IcehouseVentures\LaravelChartjs\Facades\Chartjs;
use Illuminate\Support\Facades\DB;

class FeedController extends Controller
{
  // Fetch posts from user's communities created in the last 72 hours, order them by vote quantity, caches values for 60mins
  public function home()
  {

    $cachedPosts = Cache::get('user_posts');

    if ($cachedPosts) {
      return response()->json($cachedPosts);
    }

    $authUser = Auth::user();


    $posts = Post::withCount('votes')
      ->whereIn('community_id', $authUser->communities->pluck('id'))
      ->where('creation_date', '>', now()->subHours(72))
      ->orderBy('votes_count', 'desc')
      ->get();

    foreach ($posts as $item) {
      $item->upvotes_count = Vote::whereHas('postVote', function ($query) use ($item) {
        $query->where('post_id', $item->post_id);
      })->where('upvote', true)->count();

      $item->downvotes_count = Vote::whereHas('postVote', function ($query) use ($item) {
        $query->where('post_id', $item->post_id);
      })->where('upvote', false)->count();

      $userVote = $authUser->votes()->whereHas('postVote', function ($query) use ($item) {
        $query->where('post_id', $item->post_id);
      })->first();

      if ($userVote) {
        $item->user_upvoted = $userVote->upvote;
        $item->user_downvoted = !$userVote->upvote;
      } else {
        // User hasn't voted on this post
        $item->user_upvoted = false;
        $item->user_downvoted = false;
      }
    }

    return view('pages.home', [
      'posts' => $posts
    ]);
  }
  public function global()
  {
    // Check if cached posts exist
    // $cachedPosts = Cache::get('popular_posts');

    // if ($cachedPosts) {
    //   return view('pages.global', [
    //     'posts' => $cachedPosts
    //   ]);
    // }

    // Fetch posts from public communities created within the last 72 hours
    $posts = Post::withCount('votes')
      ->whereHas('community', function ($query) {
        $query->where('privacy', false);
      })
      ->where('creation_date', '>', now()->subHours(72))
      ->orderBy('votes_count', 'desc')
      ->get();

    $authUser = Auth::user(); // For retrieving user-specific votes

    foreach ($posts as $item) {
      // Count upvotes and downvotes
      $item->upvotes_count = Vote::whereHas('postVote', function ($query) use ($item) {
        $query->where('post_id', $item->id);
      })->where('upvote', true)->count();

      $item->downvotes_count = Vote::whereHas('postVote', function ($query) use ($item) {
        $query->where('post_id', $item->id);
      })->where('upvote', false)->count();

      // Check if the authenticated user has voted on this post
      if ($authUser) {
        $userVote = $authUser->votes()->whereHas('postVote', function ($query) use ($item) {
          $query->where('post_id', $item->id);
        })->first();

        if ($userVote) {
          $item->user_upvoted = $userVote->upvote;
          $item->user_downvoted = !$userVote->upvote;
        } else {
          // User hasn't voted on this post
          $item->user_upvoted = false;
          $item->user_downvoted = false;
        }
      } else {
        // For guests, no votes are possible
        $item->user_upvoted = false;
        $item->user_downvoted = false;
      }
    }

    // Cache the posts for 60 minutes
    // Cache::put('popular_posts', $posts, 60);

    // Render the view and pass the posts
    return view('pages.global', [
      'posts' => $posts
    ]);
  }


  public function recent()
  {
    $authUser = Auth::user(); // For retrieving user-specific votes

    // Fetch posts from user's communities, sorted by creation date
    $posts = Post::withCount('votes')
      ->whereIn('community_id', $authUser->communities->pluck('id'))
      ->orderBy('creation_date', 'desc')
      ->get();

    foreach ($posts as $post) {
      // Count upvotes and downvotes
      $post->upvotes_count = Vote::whereHas('postVote', function ($query) use ($post) {
        $query->where('post_id', $post->id);
      })->where('upvote', true)->count();

      $post->downvotes_count = Vote::whereHas('postVote', function ($query) use ($post) {
        $query->where('post_id', $post->id);
      })->where('upvote', false)->count();

      // Check if the authenticated user has voted on this post
      $userVote = $authUser->votes()->whereHas('postVote', function ($query) use ($post) {
        $query->where('post_id', $post->id);
      })->first();

      if ($userVote) {
        $post->user_upvoted = $userVote->upvote;
        $post->user_downvoted = !$userVote->upvote;
      } else {
        // User hasn't voted on this post
        $post->user_upvoted = false;
        $post->user_downvoted = false;
      }
    }

    // Render the view and pass the posts collection
    return view('pages.recent', [
      'posts' => $posts,
    ]);
  }

  public function aboutUs()
  {
    return view('pages.about_us');
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

  private function newUsersChart()
  {
    // Get the date range for the last 14 days
    $startDate = now()->subDays(13)->startOfDay();
    $endDate = now()->endOfDay();

    // Fetch the data
    $data = DB::table('authenticated_users')
      ->select(DB::raw('DATE(creation_date) as registration_date'), DB::raw('COUNT(*) as new_users'))
      ->whereBetween('creation_date', [$startDate, $endDate])
      ->groupBy('registration_date')
      ->orderBy('registration_date', 'asc')
      ->get();

    // Generate a list of dates for the last 14 days
    $labels = collect();
    for ($date = $startDate->copy(); $date <= $endDate; $date->addDay()) {
      $labels->push($date->toDateString());
    }

    // Map the counts to the corresponding dates
    $counts = $labels->map(function ($date) use ($data) {
      return $data->firstWhere('registration_date', $date)?->new_users ?? 0;
    });

    // Build the chart
    $chart = Chartjs::build()
      ->name('newUsersChart')
      ->type('line')
      ->size(['width' => 400, 'height' => 200])
      ->labels($labels->toArray())
      ->datasets([
        [
          "label" => "New Users",
          "backgroundColor" => "rgba(75, 192, 192, 0.2)",
          "borderColor" => "rgba(75, 192, 192, 1)",
          "pointBorderColor" => "rgba(75, 192, 192, 1)",
          "pointBackgroundColor" => "rgba(75, 192, 192, 1)",
          "pointHoverBackgroundColor" => "#fff",
          "pointHoverBorderColor" => "rgba(75, 192, 192, 1)",
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

  private function postsPerDayChart()
  {
    // Get the date range for the last 14 days
    $startDate = now()->subDays(13)->startOfDay();
    $endDate = now()->endOfDay();

    // Fetch the data
    $data = DB::table('posts')
      ->select(DB::raw('DATE(creation_date) as post_date'), DB::raw('COUNT(*) as post_count'))
      ->whereBetween('creation_date', [$startDate, $endDate])
      ->groupBy('post_date')
      ->orderBy('post_date', 'asc')
      ->get();

    // Generate a list of dates for the last 14 days
    $labels = collect();
    for ($date = $startDate->copy(); $date <= $endDate; $date->addDay()) {
      $labels->push($date->toDateString());
    }

    // Map the counts to the corresponding dates
    $counts = $labels->map(function ($date) use ($data) {
      return $data->firstWhere('post_date', $date)?->post_count ?? 0;
    });

    // Build the chart
    $chart = Chartjs::build()
      ->name('postsPerDayChart')
      ->type('line')
      ->size(['width' => 400, 'height' => 200])
      ->labels($labels->toArray())
      ->datasets([
        [
          "label" => "Posts Per Day",
          "backgroundColor" => "rgba(255, 99, 132, 0.2)",
          "borderColor" => "rgba(255, 99, 132, 1)",
          "pointBorderColor" => "rgba(255, 99, 132, 1)",
          "pointBackgroundColor" => "rgba(255, 99, 132, 1)",
          "pointHoverBackgroundColor" => "#fff",
          "pointHoverBorderColor" => "rgba(255, 99, 132, 1)",
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

  private function postsComboChart()
  {
    // Define the date range for the past 7 days
    $startDate = now()->subDays(6)->startOfDay(); // Start 6 days ago to include today (7 days total)
    $endDate = now()->endOfDay();

    // Fetch the data for news
    $newsData = DB::table('news')
      ->join('posts', 'news.post_id', '=', 'posts.id')
      ->select(DB::raw('DATE(posts.creation_date) as post_date'), DB::raw('COUNT(*) as news_count'))
      ->whereBetween('posts.creation_date', [$startDate, $endDate])
      ->groupBy('post_date')
      ->pluck('news_count', 'post_date');

    // Fetch the data for topics
    $topicsData = DB::table('topics')
      ->join('posts', 'topics.post_id', '=', 'posts.id')
      ->select(DB::raw('DATE(posts.creation_date) as post_date'), DB::raw('COUNT(*) as topics_count'))
      ->whereBetween('posts.creation_date', [$startDate, $endDate])
      ->groupBy('post_date')
      ->pluck('topics_count', 'post_date');

    // Fetch the data for all posts
    $postsData = DB::table('posts')
      ->select(DB::raw('DATE(creation_date) as post_date'), DB::raw('COUNT(*) as total_count'))
      ->whereBetween('creation_date', [$startDate, $endDate])
      ->groupBy('post_date')
      ->pluck('total_count', 'post_date');

    // Generate the labels (list of dates)
    $labels = collect();
    for ($date = $startDate->copy(); $date <= $endDate; $date->addDay()) {
      $labels->push($date->toDateString());
    }

    // Map the data to ensure each label has a corresponding value
    $newsCounts = $labels->map(fn($date) => $newsData[$date] ?? 0);
    $topicsCounts = $labels->map(fn($date) => $topicsData[$date] ?? 0);
    $postsCounts = $labels->map(fn($date) => $postsData[$date] ?? 0);

    // Build the chart
    $chart = Chartjs::build()
      ->name('postsComboChart')
      ->type('bar') // Base type for mixed charts
      ->size(['width' => 400, 'height' => 200])
      ->labels($labels->toArray())
      ->datasets([
        [
          "type" => "bar",
          "label" => "News",
          "backgroundColor" => "rgba(255, 99, 132, 0.5)",
          "borderColor" => "rgba(255, 99, 132, 1)",
          "data" => $newsCounts->toArray(),
        ],
        [
          "type" => "bar",
          "label" => "Topics",
          "backgroundColor" => "rgba(54, 162, 235, 0.5)",
          "borderColor" => "rgba(54, 162, 235, 1)",
          "data" => $topicsCounts->toArray(),
        ],
        [
          "type" => "line",
          "label" => "Total Posts",
          "backgroundColor" => "rgba(75, 192, 192, 0.5)",
          "borderColor" => "rgba(75, 192, 192, 1)",
          "fill" => false,
          "data" => $postsCounts->toArray(),
        ]
      ])
      ->options([
        "plugins" => [
          "title" => [
            "display" => true,
            "text" => "Posts Analysis (Last 7 Days)"
          ]
        ],
        "scales" => [
          "y" => [
            "beginAtZero" => true,
            "ticks" => [
              "stepSize" => 1 // Ensures integer-only y-axis
            ]
          ],
          "x" => [
            "type" => "time",
            "time" => [
              "unit" => "day"
            ]
          ]
        ]
      ]);

    return $chart;
  }


  public function admin()
  {
    $chartHubs = FeedController::newCommunitiesChart();
    $chartUsers = FeedController::newUsersChart();
    $postsPDay = FeedController::postsPerDayChart();
    $comboPosts = FeedController::postsComboChart();

    return view('pages.admin', compact('chartHubs', 'chartUsers', 'postsPDay', 'comboPosts'));
  }


  public function bestof()
  {
      // 10 topics
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
  
      // 10 news
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
  


}
