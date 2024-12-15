@extends('layouts.app')

@section('content')
<div class="">
  <!-- Display success message if exists -->
  @if(session('success'))
  <div class="alert alert-success">
    {{ session('success') }}
  </div>
  @endif

  @php
  $activeTab = request()->query('tab', 'News'); // Default to 'News'
  @endphp

  @include('partials.news_topic_nav', ['url' => '/news/'])

  @if ($activeTab === 'News')
  @if($news->isEmpty())
  <p>No news available.</p>
  @else
  @include('partials.news_grid', ['posts' => $news->take(6)])
  <div class="divide-y-2 divide-black border-b-2 border-black">
    @foreach($news->slice(6) as $post)
    @include('partials.post', [
    'news' => 'true',
    'post' => $post->news,
    'item' => $post
    ])
    @endforeach
  </div>
  @endif
  @elseif ($activeTab === 'Topics')
  @if($topics->isEmpty())
  <p>No topics available.</p>
  @else
  <div class="divide-y-2 divide-black border-b-2 border-black">
    @foreach($topics as $topic)
    @include('partials.post', ['news' => false, 'post' => $topic->topic, 'img' => false, 'item' => $topic])
    @endforeach
  </div>
  @endif

  @endif


</div>


<script>
  //   document.getElementById('content-type-selector').addEventListener('change', function () {
// window.location.href = this.value;
// });


  const voteButtons = document.querySelectorAll("input[type='checkbox']");

  voteButtons.forEach((button) => {
    button.addEventListener("change", async function () {
      const postId = this.id.split("-")[0]; 
      const voteType = this.id.includes("upvote") ? "upvote" : "downvote";
      const isChecked = this.checked;
      const otherVote = document.getElementById(`${postId}-${voteType == "upvote" ? "downvote" : "upvote"}`)
      console.log(otherVote)
      console.log(button)

      try {
        const response = await fetch(`/news/${postId}/voteupdate`, {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute("content"),
          },
          body: JSON.stringify({
            vote_type: voteType,
          }),
        });

        if (response.ok) {
          const data = await response.json();
          console.log(data);
          console.log(data.vote === voteType)
          
          const scoreElement = document.getElementById(`${postId}-score`);
          if (scoreElement) {
            let newScore = data.newScore;
            if (!scoreElement.textContent.includes("k")) {
              let currentScore = parseInt(scoreElement.textContent.replace(/[^\d.-]/g, ''));
              newScore = currentScore + newScore;
              scoreElement.textContent = newScore >= 1000 ? `${(newScore / 1000).toFixed(1)}k` : newScore;
            }
          }

          if (data.vote === voteType) {          
            this.checked = true
            otherVote.checked = false // exclusive
          } else {
            this.checked = false
          }

        } else {
          console.error("Failed to update the vote:", await response.text());
        }
      } catch (error) {
        console.error("Error while updating the vote:", error);
      }
    });
  });
</script>


@endsection