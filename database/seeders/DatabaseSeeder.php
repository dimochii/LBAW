<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\News;
use DOMDocument;
use DOMXPath;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
  /**
   * Seed the application's database.
   */
  public function run(): void
  {
    $path = base_path('database/create_db.sql');
    $sql = file_get_contents($path);
    DB::unprepared($sql);

    foreach (News::all() as $news) {
      $ogTags = $this->getOgTags($news->news_url);

      $post = $news->post;
      $post->title = $post->title ?? $ogTags['title'];
      $post->save();

      $news->image_url = $ogTags['image'] ?? $news->image_url;
      $news->save();
    }

    $this->command->info('Database seeded!');
  }

  private function getOgTags($newsURL)
  {
    $ogTags = [];
    libxml_use_internal_errors(true);

    // create curl resource
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, $newsURL);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36");

    $c = curl_exec($ch);

    curl_close($ch);

    if (empty($c)) {
      return $ogTags;
    }

    $d = new DOMDocument();
    $d->loadHTML($c);
    $xp = new DOMXPath($d);


    $imageElement = $xp->query("//meta[@property='og:image']")->item(0);
    if ($imageElement) {
      $ogTags['image'] = $imageElement->getAttribute("content");
    }

    $titleElement = $xp->query("//meta[@property='og:title']")->item(0);
    if ($titleElement) {
      $ogTags['title'] = $titleElement->getAttribute("content");
    }

    return $ogTags;
  }
}
