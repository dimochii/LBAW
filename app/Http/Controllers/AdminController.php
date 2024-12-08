<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AuthenticatedUser;
use App\Models\Community;
use App\Models\News;
use App\Models\Topic;
use App\Models\Post;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use IcehouseVentures\LaravelChartjs\Facades\Chartjs;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
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
          "label" => "New hubs",
          "backgroundColor" => "rgba(237, 215, 90, 0.2)",
          "borderColor" => "rgba(237, 215, 90, 1)",
          "pointBorderColor" => "rgba(237, 215, 90, 1)",
          "pointBackgroundColor" => "rgba(237, 215, 90, 1)",
          "pointHoverBackgroundColor" => "#fff",
          "pointHoverBorderColor" => "rgba(237, 215, 90, 1)",
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
    $startDate = now()->subDays(13)->startOfDay(); // Start 6 days ago to include today (7 days total)
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
            "text" => "Posts Analysis"
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
  public function overview()
  {
    $users = AuthenticatedUser::all();
    $hubs = Community::all();
    $news = News::all();
    $topics = Topic::all();
    $chartHubs = $this->newCommunitiesChart();
    $chartUsers = $this->newUsersChart();
    $postsPDay = $this->postsPerDayChart();
    $comboPosts = $this->postsComboChart();

    return view('pages.admin', compact(
      'users',
      'hubs',
      'news',
      'topics',
      'chartHubs',
      'chartUsers',
      'postsPDay',
      'comboPosts'
    ));
  }

  public function users()
  {
    $users = AuthenticatedUser::all();
    $chartUsers = $this->newUsersChart();

    $date_span = Carbon::now()->subDays(13);
    $startDate = now()->subDays(13)->toFormattedDateString();
    $endDate = now()->toFormattedDateString();

    $suspendedUserCount = AuthenticatedUser::where('is_suspended', true)->count();
    $activeUserCount = DB::table('authenticated_users')
      // Join authors table to get users who have authored posts in the last 30 days
      ->leftJoin('authors', 'authenticated_users.id', '=', 'authors.authenticated_user_id')
      // Join posts table to get posts' creation date for filtering
      ->leftJoin('posts', 'authors.post_id', '=', 'posts.id')
      // Join comments table to get users who have posted comments in the last 30 days
      ->leftJoin('comments', 'authenticated_users.id', '=', 'comments.authenticated_user_id')
      // Filter by posts or comments created in the last 30 days
      ->where(function ($query) {
        $query->where('posts.creation_date', '>=', Carbon::now()->subDays(30))
          ->orWhere('comments.creation_date', '>=', Carbon::now()->subDays(30));
      })
      // Exclude suspended users
      ->where('authenticated_users.is_suspended', false)
      // Get distinct users who have either posted a comment or authored a post
      ->distinct()
      ->count('authenticated_users.id');

    $newUserCount = AuthenticatedUser::whereDate('creation_date', '>', $date_span)->count();

    return view('pages.admin_users', compact(
      'users',
      'chartUsers',
      'startDate',
      'endDate',
      'newUserCount',
      'suspendedUserCount',
      'activeUserCount'
    ));
  }

  public function hubs()
  {
    $hubs = Community::all();
    $chartHubs = $this->newCommunitiesChart();
    $date_span = Carbon::now()->subDays(13);

    $startDate = now()->subDays(13)->toFormattedDateString();
    $endDate = now()->toFormattedDateString();

    $totalHubs = $hubs->count();
    $newHubs = Community::whereDate('creation_date', '>', $date_span)->count();

    $totalMods = DB::table('community_moderators')->count();

    $activeHubs = DB::table('communities')
      ->join('posts', 'communities.id', '=', 'posts.community_id')
      ->where('posts.creation_date', '>', $date_span)
      ->select('communities.id', 'communities.name', 'communities.description')
      ->groupBy('communities.id')
      ->get()
      ->count();

    return view('pages.admin_hubs', compact(
      'hubs',
      'chartHubs',
      'startDate',
      'endDate',
      'totalHubs',
      'newHubs',
      'totalMods',
      'activeHubs'

    ));
  }

  public function posts()
  {
    $date_span = Carbon::now()->subDays(13);

    $startDate = now()->subDays(13)->toFormattedDateString();
    $endDate = now()->toFormattedDateString();

    $activeTab = request()->query('tab', 'news');
    if ($activeTab == 'topics') {
      $data = Topic::all();
    } else {
      $data = News::all();
    }

    $topicsCount = Topic::all()->count();
    $newsCount = News::all()->count();

    $newTopicsCount = Topic::whereHas('post', function ($query) use ($date_span) {
      $query->whereDate('creation_date', '>', $date_span);
    })->get()->count();

    $newNewsCount = News::whereHas('post', function ($query) use ($date_span) {
      $query->whereDate('creation_date', '>', $date_span);
    })->get()->count();

    $comboPosts = $this->postsComboChart();

    return view('pages.admin_posts', compact(
      'data',
      'comboPosts',
      'startDate',
      'endDate',
      'topicsCount',
      'newsCount',
      'newNewsCount',
      'newTopicsCount'
    ));
  }
}
