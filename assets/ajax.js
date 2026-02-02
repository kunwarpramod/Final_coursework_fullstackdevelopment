function updateVotes(electionId) {
  setInterval(function(){
    fetch("../ajax/count_votes.php?election_id=" + encodeURIComponent(electionId))
      .then(r => r.text())
      .then(html => {
        const el = document.getElementById("voteCount");
        if (el) el.innerHTML = html;
      })
      .catch(err => console.error("Vote update error:", err));
  }, 3000);
}

// Live AJAX search has been removed; server-side GET search is used for admin pages.
// Keep `updateVotes()` in this file for live vote updates from other pages.

// (Live-search helper removed)

