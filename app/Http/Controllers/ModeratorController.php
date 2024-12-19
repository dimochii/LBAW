<?php

namespace App\Http\Controllers;


use App\Models\Community;
use App\Models\News;
use App\Models\Topic;
use App\Models\AuthenticatedUser;
use Carbon\Carbon;
use IcehouseVentures\LaravelChartjs\Facades\Chartjs;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ModeratorController extends Controller
{
  public function show(Request $request)
  {

    $moderated_hubs = Auth::user()->moderatedCommunities;

    $selected_hub = null;

    if ($request->has('hub_id')) {
      $selected_hub = $moderated_hubs->firstWhere('id', $request->hub_id);

      if ($selected_hub) {
        $selected_hub->load(['posts', 'moderators', 'followers']);
      }
    }

    return view('pages.moderator', compact(
      'moderated_hubs',
      'selected_hub'
    ));
  }

  public function overview($id)
  {
    return redirect('/hub/' . $id . '/moderation/users');
  }

  public function  makeModerator($user_id, $community_id)
  {
      $community = Community::find($community_id);
      $userToAdd = AuthenticatedUser::find($user_id);

      if (!Auth::user()->moderatedCommunities->contains($community) && !Auth::user()->is_admin) {
        return response()->view('errors.403', [], 403);
      }
      if ($community->moderators->contains($userToAdd)) {
        return response()->view('errors.400', [], 400);
      }

        $community->moderators()->attach($userToAdd);

        return response()->json(['message' => 'User gained moderator privileges successfully']);
    }
    
    public function removeModerator($user_id, $community_id)
    {
        $community = Community::find($community_id);
        $userToRemove = AuthenticatedUser::find($user_id);

        if (!Auth::user()->moderatedCommunities->contains($community) && !Auth::user()->is_admin) {
            return response()->view('errors.403', [], 403);
        }

        if (!$community->moderators->contains($userToRemove)) {
            return response()->view('errors.400', [], 400);
        }

        $community->moderators()->detach($userToRemove);

        return response()->json(['message' => 'User has been removed from moderator successfully.']);
    }

    public function removeFollower($user_id,$community_id)
    {
        $community = Community::findOrFail($community_id);
        $user = AuthenticatedUser::findOrFail($user_id);

        if (!Auth::user()->moderatedCommunities->contains($community) && !Auth::user()->is_admin) {
          return response()->view('errors.403', [], 403);
        }

        if (!$community->followers->contains($user)) {
          return response()->view('errors.400', [], 400);
        }

        $community->followers()->detach($user);

        return redirect()->back()->with('success', 'removed follower.');
    }


  private function activeUsersChart($id)
  {
    // Get the date range for the last 14 days
    $startDate = now()->subDays(13)->startOfDay();
    $endDate = now()->endOfDay();


    // Get active users who have posted in the community in the last 14 days (authors)
    $postsData = DB::table('authenticated_users')
      // Join the authors table to get users who have authored a post in the community
      ->join('authors', function ($join) use ($id, $startDate, $endDate) {
        $join->on('authenticated_users.id', '=', 'authors.authenticated_user_id')
          ->join('posts', 'posts.id', '=', 'authors.post_id')
          ->where('posts.community_id', '=', $id)
          ->whereBetween('posts.creation_date', [$startDate, $endDate]);
      })
      ->select(DB::raw('DATE(posts.creation_date) as activity_date'), 'authenticated_users.id')
      ->groupBy(DB::raw('DATE(posts.creation_date)'), 'authenticated_users.id');

    // Get active users who have commented on posts in the community in the last 14 days
    $commentsData = DB::table('authenticated_users')
      // Join the comments table to get users who have commented in the community
      ->join('comments', 'authenticated_users.id', '=', 'comments.authenticated_user_id')
      ->join('posts', 'posts.id', '=', 'comments.post_id')
      ->where('posts.community_id', '=', $id)
      ->whereBetween('comments.creation_date', [$startDate, $endDate])
      ->select(DB::raw('DATE(comments.creation_date) as activity_date'), 'authenticated_users.id')
      ->groupBy(DB::raw('DATE(comments.creation_date)'), 'authenticated_users.id');

    // Combine posts and comments data using union
    $allData = $postsData->union($commentsData)
      ->orderBy('activity_date', 'asc')
      ->get();

    // Generate a list of dates for the last 14 days
    $labels = collect();
    for ($date = $startDate->copy(); $date <= $endDate; $date->addDay()) {
      $labels->push($date->toDateString());
    }

    // Map the counts to the corresponding dates
    $counts = $labels->map(function ($date) use ($allData) {
      // Count distinct users for each day
      return $allData->where('activity_date', $date)->pluck('id')->unique()->count();
    });

    // Build the chart
    $chart = Chartjs::build()
      ->name('activeUsersChart')
      ->type('line')
      ->size(['width' => 400, 'height' => 200])
      ->labels($labels->toArray())
      ->datasets([
        [
          "label" => "Active Users",
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

  public function users($id)
  {
    $startDate = Carbon::now()->subDays(13);
    $endDate = Carbon::now();
    $hub = Community::findOrFail($id);
    $activeUserChart = $this->activeUsersChart($id);

    $postsData = DB::table('authenticated_users')
      ->join('authors', function ($join) use ($id, $startDate, $endDate) {
        $join->on('authenticated_users.id', '=', 'authors.authenticated_user_id')
          ->join('posts', 'posts.id', '=', 'authors.post_id')
          ->where('posts.community_id', '=', $id)
          ->whereBetween('posts.creation_date', [$startDate, $endDate]);
      })
      ->select(DB::raw('DATE(posts.creation_date) as activity_date'), 'authenticated_users.id')
      ->groupBy(DB::raw('DATE(posts.creation_date)'), 'authenticated_users.id');

    $commentsData = DB::table('authenticated_users')
      ->join('comments', 'authenticated_users.id', '=', 'comments.authenticated_user_id')
      ->join('posts', 'posts.id', '=', 'comments.post_id')
      ->where('posts.community_id', '=', $id)
      ->whereBetween('comments.creation_date', [$startDate, $endDate])
      ->select(DB::raw('DATE(comments.creation_date) as activity_date'), 'authenticated_users.id')
      ->groupBy(DB::raw('DATE(comments.creation_date)'), 'authenticated_users.id');

    $activeUserCount = $postsData->union($commentsData)
      ->get()
      ->count();

    $moderatorCount = $hub->moderators()->count();

    $followers = $hub->followers();

    return view('pages.moderator', compact(
      'startDate',
      'endDate',
      'id',
      'hub',
      'activeUserChart',
      'activeUserCount',
      'followers',
      'moderatorCount',
    ));
  }

  private function reportActivityChart($communityId)
  {
    // Get the date range for the last 14 days
    $startDate = now()->subDays(13)->startOfDay();
    $endDate = now()->endOfDay();

    // Get the number of reports for the specified community in the last 14 days
    $reportsData = DB::table('reports')
      // Join with the posts table to filter by community
      ->join('posts', 'posts.id', '=', 'reports.reported_id')
      ->where('posts.community_id', '=', $communityId)
      ->whereBetween('reports.report_date', [$startDate, $endDate])
      ->select(DB::raw('DATE(reports.report_date) as activity_date'), DB::raw('COUNT(*) as report_count'))
      ->groupBy(DB::raw('DATE(reports.report_date)'))
      ->orderBy('activity_date', 'asc')
      ->get();

    // Generate a list of dates for the last 14 days
    $labels = collect();
    for ($date = $startDate->copy(); $date <= $endDate; $date->addDay()) {
      $labels->push($date->toDateString());
    }

    // Map the counts to the corresponding dates (fill missing days with 0 reports)
    $counts = $labels->map(function ($date) use ($reportsData) {
      // Check if there are reports for the given date, if not return 0
      $report = $reportsData->firstWhere('activity_date', $date);
      return $report ? $report->report_count : 0;
    });

    // Build the chart
    $chart = Chartjs::build()
      ->name('reportActivityChart')
      ->type('line')
      ->size(['width' => 400, 'height' => 200])
      ->labels($labels->toArray())
      ->datasets([
        [
          "label" => "Reports per Day",
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

  public function reports($id)
  {
    $startDate = Carbon::now()->subDays(13);
    $endDate = Carbon::now();
    $hub = Community::findOrFail($id);
    $reports = $hub->reports();
    $pendingReportsCount = $hub->reports()->where('is_open', true)->count();
    $resolvedReportsCount = $hub->reports()->where('is_open', false)->count();
    $reportsChart = $this->reportActivityChart($id);

    return view('pages.moderator_reports', compact(
      'startDate',
      'endDate',
      'id',
      'hub',
      'reports',
      'pendingReportsCount',
      'resolvedReportsCount',
      'reportsChart',

    ));
  }

  private function postsComboChart($communityId)
  {
    // Define the date range for the past 7 days
    $startDate = now()->subDays(13)->startOfDay(); // Start 13 days ago to include today (7 days total)
    $endDate = now()->endOfDay();

    // Fetch the data for news
    $newsData = DB::table('news')
      ->join('posts', 'news.post_id', '=', 'posts.id')
      ->select(DB::raw('DATE(posts.creation_date) as post_date'), DB::raw('COUNT(*) as news_count'))
      ->where('posts.community_id', $communityId) // Filter by community ID
      ->whereBetween('posts.creation_date', [$startDate, $endDate])
      ->groupBy('post_date')
      ->pluck('news_count', 'post_date');

    // Fetch the data for topics
    $topicsData = DB::table('topics')
      ->join('posts', 'topics.post_id', '=', 'posts.id')
      ->select(DB::raw('DATE(posts.creation_date) as post_date'), DB::raw('COUNT(*) as topics_count'))
      ->where('posts.community_id', $communityId) // Filter by community ID
      ->whereBetween('posts.creation_date', [$startDate, $endDate])
      ->groupBy('post_date')
      ->pluck('topics_count', 'post_date');

    // Fetch the data for all posts
    $postsData = DB::table('posts')
      ->select(DB::raw('DATE(creation_date) as post_date'), DB::raw('COUNT(*) as total_count'))
      ->where('community_id', $communityId) // Filter by community ID
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


  public function posts($id)
  {
    $startDate = Carbon::now()->subDays(13);
    $endDate = Carbon::now();
    $hub = Community::findOrFail($id);
    $postsChart = $this->postsComboChart($id);

    $activeTab = request()->query('tab', 'news');

    if ($activeTab == 'topics') {
      $data = Topic::whereHas('post', function ($query) use ($id) {
        $query->where('community_id', $id);
      })->get();
    } else {
      $data = News::whereHas('post', function ($query) use ($id) {
        $query->where('community_id', $id);
      })->get();
    }

    $newsCount = Topic::whereHas('post', function ($query) use ($id) {
      $query->where('community_id', $id);
    })->get()->count();

    $topicsCount = News::whereHas('post', function ($query) use ($id) {
      $query->where('community_id', $id);
    })->get()->count();

    return view('pages.moderator_posts', compact(
      'startDate',
      'endDate',
      'id',
      'hub',
      'postsChart',
      'data',
      'newsCount',
      'topicsCount',
    ));
  }

}
