<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AuthenticatedUser;
use App\Models\Community;
use App\Models\News;
use App\Models\Topic;
use App\Models\Report;
use App\Models\Post;
use App\Models\FollowNotification;
use App\Models\Suspension;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use IcehouseVentures\LaravelChartjs\Facades\Chartjs;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
  public function  makeAdmin($id)
    {
        
        if (!$this->authorize('isAdmin', Auth::user())) {
            return response()->view('errors.403', [], 403); 
        }

        $user = AuthenticatedUser::findOrFail($id);
        $user->is_admin = true;
        $user->save();

        return response()->json(['message' => 'User gained admin privileges successfully']);
    }
    public function  removeAdmin($id)
    {
        if (!$this->authorize('isAdmin', Auth::user())) {
            return response()->view('errors.403', [], 403); 
        }

        $user = AuthenticatedUser::findOrFail($id);
        $user->is_admin = false;
        $user->save();

        return response()->json(['message' => 'User lost admin privileges successfully']);
    }
    
    public function suspend($id, Request $request)
    {
      if (!$this->authorize('isAdmin', Auth::user())) {
        return response()->view('errors.403', [], 403); 
    }

        $request->validate([
            'reason' => 'required|string',
            'duration' => 'required|integer|min:1',
        ]);

        $user = AuthenticatedUser::findOrFail($id);

        $user->is_suspended = true;
        $user->save();

        $suspension = new Suspension([
            'reason' => $request->input('reason'),
            'start' => now(),
            'duration' => $request->input('duration'),  
            'authenticated_user_id' => $user->id, 
        ]);

        $suspension->save();

        return response()->json(['message' => 'User suspended successfully.']);
    }

    public function unsuspend($id)
    {
      if (!$this->authorize('isAdmin', Auth::user())) {
        return response()->view('errors.403', [], 403); 
      }

        $user = AuthenticatedUser::findOrFail($id);
        
        $user->is_suspended = false;
        $user->save();

        $user->suspensions()->delete();

        return response()->json(['message' => 'User unsuspended successfully.']);
    }

    public function deleteUserAccount(Request $request, $id) {
      $admin = Auth::user();
      
      if (!$this->authorize('isAdmin', Auth::user())) {
        return response()->view('errors.403', [], 403); 
    }
  
      $user = AuthenticatedUser::find($id);
      $deletedUserId = 1;
      $deletedUser = AuthenticatedUser::find($deletedUserId);

      //Update votes --> deleted user
      if ($user->votes()->exists()) {
          $user->votes()->update(['authenticated_user_id' => $deletedUserId]);
      }

      //Update comments --> deleted user
      if ($user->comments()->exists()) {
          $user->comments()->update(['authenticated_user_id' => $deletedUserId]);
      }

      //update post ---> solo writer --> deleted user// co-author ---> just remove
      foreach ($user->authoredPosts as $post) {
          $authorCount = $post->authors()->count();
          if ($authorCount === 1) {
              $post->update(['authenticated_user_id' => $deletedUserId]);
              $post->authors()->syncWithoutDetaching([$deletedUserId]); 
              $post->authors()->detach($user->id); 
          } 

          else {
              $post->authors()->detach($user); 
              //$post->authors()->attach($deletedUser); // Add the deleted user as an author
          }
      }
   

      //delete user notifications....
      if ($user->notifications()->exists()) {
          $user->notifications()->update(['authenticated_user_id' => $deletedUserId]);
      }
  
      //erase reports with user.....
      if ($user->reports()->exists()) {
          $user->reports()->delete(); 
      }
      //erase suspensions with user...
      if ($user->suspensions()->exists()) {
          $user->suspensions()->delete(); 
      }

      FollowNotification::where('follower_id', $user->id)
      ->update(['follower_id' => $deletedUserId]);

      
      $user->moderatedCommunities()->detach();
      $user->favouritePosts()->detach();
      $user->communities()->detach();
      $user->follows()->detach();
      $user->followers()->detach();
      $user->delete();
  
      return redirect()->route('admin.users')->with('message', 'User account has been successfully deleted.');
      
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

  private function pieChartUsersSuspended()
  {

    $suspendedUsers = AuthenticatedUser::where('is_suspended', true)->count();
    $normalUsers = AuthenticatedUser::where('is_suspended', false)->count();


    $data = [$suspendedUsers, $normalUsers];
    $labels = ['Suspended Users', 'Normal Users'];
    $backgroundColor = ['#C96868', '#7EACB5'];

    $chart = Chartjs::build()
      ->name('userStatusChart')
      ->type('pie')
      ->size(['width' => 400, 'height' => 400])
      ->labels($labels)
      ->datasets([
        [
          "data" => $data,
          "backgroundColor" => $backgroundColor
        ]
      ])
      ->options([
        "responsive" => true,
        "plugins" => [
          "legend" => [
            "display" => false // Hide legend
          ],
        ]
      ]);

    return $chart;
  }


  public function overview()
  {

    $chartHubs = $this->newCommunitiesChart();
    $chartUsers = $this->newUsersChart();
    $chartReports = $this->newReportsChart();
    $postsPDay = $this->postsPerDayChart();
    $comboPosts = $this->postsComboChart();

    $latestNews = News::latest('post_id')->take(5)->get();
    $latestTopic = Topic::latest('post_id')->take(5)->get();
    $pieSuspended = $this->pieChartUsersSuspended();

    $startDate = Carbon::now()->subDays(13);
    $endDate = Carbon::now();

    $hottestPosts = Post::withCount('votes', 'comments')
      ->where('creation_date', '>=', $startDate)
      ->take(10)
      ->get();

    $hottestPosts = $hottestPosts->map(function ($post) {
      $post->total_engagement = $post->votes_count + $post->comments_count;
      return $post;
    })->sortByDesc('total_engagement');

    $topUsers = AuthenticatedUser::withCount('followers', 'authoredPosts')->orderBy('followers_count', 'desc')->take(5)->get();
    $topHubs = Community::withCount('followers')->orderBy('followers_count', 'desc')->take(5)->get();
    
    $userCount = AuthenticatedUser::all()->count();
    $activeUserCount = DB::table('authenticated_users')
      ->leftJoin('authors', 'authenticated_users.id', '=', 'authors.authenticated_user_id')
      ->leftJoin('posts', 'authors.post_id', '=', 'posts.id')
      ->leftJoin('comments', 'authenticated_users.id', '=', 'comments.authenticated_user_id')
      ->where(function ($query) {
        $query->where('posts.creation_date', '>=', Carbon::now()->subDays(30))
          ->orWhere('comments.creation_date', '>=', Carbon::now()->subDays(30));
      })
      // Exclude suspended users
      ->where('authenticated_users.is_suspended', false)
      // Get distinct users who have either posted a comment or authored a post
      ->distinct()
      ->count('authenticated_users.id');

    $hubCount = Community::all()->count();
    $activeHubCount = DB::table('communities')
      ->join('posts', 'communities.id', '=', 'posts.community_id')
      ->where('posts.creation_date', '>', $startDate)
      ->select('communities.id', 'communities.name', 'communities.description')
      ->groupBy('communities.id')
      ->get()
      ->count();

    $pendingReports = Report::where('is_open', true)->count();

    $newTopicsCount = Topic::whereHas('post', function ($query) use ($startDate) {
      $query->whereDate('creation_date', '>', $startDate);
    })->get()->count();

    $newNewsCount = News::whereHas('post', function ($query) use ($startDate) {
      $query->whereDate('creation_date', '>', $startDate);
    })->get()->count();

    $postsCount = Post::all()->count();
    $newPosts = $newNewsCount + $newTopicsCount;

    $mostReportedUsers = Report::select('authenticated_user_id', DB::raw('COUNT(*) as report_count'))
    ->groupBy('authenticated_user_id')
    ->orderByDesc('report_count')
    ->take(5)
    ->with('user')
    ->get();

    return view('pages.admin', compact(
      'pieSuspended',
      'topUsers',
      'topHubs',
      'latestNews',
      'latestTopic',
      'hottestPosts',
      'chartHubs',
      'chartUsers',
      'chartReports',
      'postsPDay',
      'comboPosts',
      'startDate',
      'endDate',
      'newPosts',
      'activeHubCount',
      'activeUserCount',
      'pendingReports',
      'userCount',
      'hubCount',
      'postsCount',
      'mostReportedUsers',
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
      ->leftJoin('authors', 'authenticated_users.id', '=', 'authors.authenticated_user_id')
      ->leftJoin('posts', 'authors.post_id', '=', 'posts.id')
      ->leftJoin('comments', 'authenticated_users.id', '=', 'comments.authenticated_user_id')
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

  public function reports()
  {
    // Define the date range for the past 14 days
    $date_span = Carbon::now()->subDays(13);
    $startDate = now()->subDays(13)->toFormattedDateString();
    $endDate = now()->toFormattedDateString();

    // Fetch all reports
    $reports = Report::all();

    // Total reports
    $totalReports = $reports->count();

    // New reports in the last 14 days
    $newReports = Report::whereDate('report_date', '>', $date_span)->count();

    // Resolved reports (using 'status' to indicate resolved reports)
    $resolvedReports = Report::where('is_open', false)->count(); // Assuming resolved reports are closed (is_open = false)

    // Pending reports
    $pendingReports = Report::where('is_open', true)->count(); // Pending reports are those still open

    // Generate charts
    $chartReports = $this->newReportsChart();
    $statusChart = $this->reportsStatusChart();

    return view('pages.admin_reports', compact(
      'reports',
      'totalReports',
      'newReports',
      'resolvedReports',
      'pendingReports',
      'startDate',
      'endDate',
      'chartReports',
      'statusChart'
    ));
  }



  private function newReportsChart()
  {
    // Define the date range for the past 14 days
    $startDate = now()->subDays(13)->startOfDay();
    $endDate = now()->endOfDay();

    // Fetch the data for new reports
    $data = Report::select(DB::raw('DATE(report_date) as report_date'), DB::raw('COUNT(*) as new_reports'))
      ->whereBetween('report_date', [$startDate, $endDate])
      ->groupBy('report_date')
      ->orderBy('report_date', 'asc')
      ->get();

    // Generate a list of dates for the last 14 days
    $labels = collect();
    for ($date = $startDate->copy(); $date <= $endDate; $date->addDay()) {
      $labels->push($date->toDateString());
    }

    // Map the counts to the corresponding dates
    $counts = $labels->map(function ($date) use ($data) {
      return $data->firstWhere('report_date', $date)?->new_reports ?? 0;
    });

    // Build the chart
    $chart = Chartjs::build()
      ->name('newReportsChart')
      ->type('line')
      ->size(['width' => 400, 'height' => 200])
      ->labels($labels->toArray())
      ->datasets([
        [
          "label" => "New Reports",
          "backgroundColor" => "rgba(255, 159, 64, 0.2)",
          "borderColor" => "rgba(255, 159, 64, 1)",
          "pointBorderColor" => "rgba(255, 159, 64, 1)",
          "pointBackgroundColor" => "rgba(255, 159, 64, 1)",
          "pointHoverBackgroundColor" => "#fff",
          "pointHoverBorderColor" => "rgba(255, 159, 64, 1)",
          "data" => $counts->toArray(),
          "fill" => false,
        ]
      ])
      ->options([
        "scales" => [
          "y" => [
            "beginAtZero" => true,
            "ticks" => [
              "stepSize" => 1, // Ensure integer-only y-axis
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

  private function reportsStatusChart()
  {
    // Fetch data for report statuses based on the 'is_open' field
    $statuses = Report::select('is_open', DB::raw('COUNT(*) as count'))
      ->groupBy('is_open')
      ->pluck('count', 'is_open');

    // Map the 'is_open' values to the status labels
    $labels = ['Pending', 'Resolved'];
    $counts = [
      $statuses->get(true, 0),  // Pending reports (open)
      $statuses->get(false, 0), // Resolved reports (closed)
    ];

    // Build the chart
    $chart = Chartjs::build()
      ->name('reportsStatusChart')
      ->type('doughnut')
      ->size(['width' => 400, 'height' => 200])
      ->labels($labels)
      ->datasets([
        [
          "label" => "Report Statuses",
          "backgroundColor" => ["rgba(75, 192, 192, 0.2)", "rgba(255, 99, 132, 0.2)"],
          "borderColor" => ["rgba(75, 192, 192, 1)", "rgba(255, 99, 132, 1)"],
          "data" => $counts,
        ]
      ])
      ->options([
        "plugins" => [
          "title" => [
            "display" => true,
            "text" => "Report Statuses"
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
}
