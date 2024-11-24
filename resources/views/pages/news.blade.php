@extends('layouts.app')

@section('content')
<div class="container">
  <!-- Display success message if exists -->
  @if(session('success'))
  <div class="alert alert-success">
    {{ session('success') }}
  </div>
  @endif

  <!-- Button to go to the post creation page -->
  {{-- <div style="margin-bottom: 20px;">
    <a href="{{ route('post.create') }}" class="btn btn-primary">Create New Post</a>
  </div> --}}

  <!-- Check if there are any news items -->
  @if($news->isEmpty())
  <p>No news available.</p>
  @else
  @include('partials.news_grid', ['posts' => $news->take(6)])
  <div class="divide-y-2 divide-black border-b-2 border-black">
    @foreach($news->slice(6) as $item)
    @include('partials.post', [
    'news' => 'true',
    'post' => $item,
    ])
    @endforeach
  </div>
  @endif
  <script>
    const voteButtons = document.querySelectorAll("input[type='checkbox']");

voteButtons.forEach((button) => {
  
  button.addEventListener("change", async function () {
    const postId = this.id.split("-")[0]; // Extract the post_id from the input's ID
    const voteType = this.id.includes("upvote") ? "upvote" : "downvote";
    const isChecked = this.checked;

    try {
      // Make an asynchronous request to update the vote
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

        // Update the score of the specific post
        const scoreElement = document.getElementById(`${postId}-score`);
        if (scoreElement) {
          let newScore = data.newScore;

          // If the score already contains a "k", don't modify it
          if (!scoreElement.textContent.includes("k")) {
            // If the current score doesn't have a "k", add the new score to it
            let currentScore = parseInt(scoreElement.textContent.replace(/[^\d.-]/g, '')); // Get the current numerical score
            newScore = currentScore + newScore; // Add the new score to the existing score
            scoreElement.textContent = newScore >= 1000 ? `${(newScore / 1000).toFixed(1)}k` : newScore;
          }
        }

        if (data.vote === voteType) {          
          this.checked = true
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
</div>
@endsection