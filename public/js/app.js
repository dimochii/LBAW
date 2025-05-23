const searchInput = document.getElementById('search-input')
const table = document.querySelector('table')
const tableBody = table ? table.querySelector('tbody') : null
const rows = tableBody ? tableBody.querySelectorAll('tr') : null
const headers = table ? table.querySelectorAll('th') : null
const statusFilter = document.getElementById('status-filter')
const suspensionFilter = document.getElementById('suspension-filter')
const adminFilter = document.getElementById('admin-filter')
const privacyFilter = document.getElementById('privacy-filter')
const reportsStatusFilter = document.getElementById('reports-status-filter')
const reportsTypeFilter = document.getElementById('reports-type-filter')
const moderatorFilter = document.getElementById('moderator-filter')


const directions = headers ? Array.from(headers).map(function (header) {
  return '';
}) : null

const transform = function (index, content) {
  const type = headers[index].getAttribute('data-type');
  switch (type) {
    case 'number':
      return parseFloat(content);
    case 'string':
    default:
      return content;
  }
}

const threadPlaceholder = document.getElementById('thread-placeholder')
const threadEditor = document.getElementById('thread-editor')

function addEventListeners() {
  let itemCheckers = document.querySelectorAll('article.card li.item input[type=checkbox]');
  [].forEach.call(itemCheckers, function (checker) {
    checker.addEventListener('change', sendItemUpdateRequest)
  })

  let itemCreators = document.querySelectorAll('article.card form.new_item');
  [].forEach.call(itemCreators, function (creator) {
    creator.addEventListener('submit', sendCreateItemRequest)
  })

  let itemDeleters = document.querySelectorAll('article.card li a.delete');
  [].forEach.call(itemDeleters, function (deleter) {
    deleter.addEventListener('click', sendDeleteItemRequest)
  })

  let cardDeleters = document.querySelectorAll('article.card header a.delete');
  [].forEach.call(cardDeleters, function (deleter) {
    deleter.addEventListener('click', sendDeleteCardRequest)
  })

  let cardCreator = document.querySelector('article.card form.new_card');
  if (cardCreator != null)
    cardCreator.addEventListener('submit', sendCreateCardRequest)

  const voteButtons = document.querySelectorAll('input[type="checkbox"][id$="-upvote"], input[type="checkbox"][id$="-downvote"]')
  voteButtons.forEach((button) => {
    button.addEventListener("change", voteUpdate)
  })

  const commentVoteButtons = document.querySelectorAll('input[type="checkbox"][id$="-upvote-c"], input[type="checkbox"][id$="-downvote-c"]')
  commentVoteButtons.forEach((button) => {
    button.addEventListener("change", commentVoteUpdate)
  })

  const accept = document.querySelectorAll('li[id^="accept-"]')
  accept.forEach((ele) => {
    const parsedId = ele.id.split('-')[1]
    ele.addEventListener('click', () => {
      acceptTopic(parsedId)
    });
  })

  const reject = document.querySelectorAll('li[id^="reject-"]')
  reject.forEach((ele) => {
    const parsedId = ele.id.split('-')[1]
    ele.addEventListener('click', () => {
      rejectTopic(parsedId)
    })
  })

  if (headers) {
    headers.forEach(function (header, index) {
      if (header.hasAttribute('data-type')) {
        header.addEventListener('click', function () {
          sortColumn(index)
        })
      }
    })
  }

  if (table && searchInput) {
    searchInput.addEventListener('input', function () {
      filterTable(searchInput.value)
    })
  }

  if (statusFilter) {
    statusFilter.addEventListener('change', () => {
      filterTable(searchInput.value)
    })
  }

  if (adminFilter) {
    adminFilter.addEventListener('change', () => {
      filterTable(searchInput.value)
    })
  }

  if (suspensionFilter) {
    suspensionFilter.addEventListener('change', () => {
      filterTable(searchInput.value)
    })
  }

  if (privacyFilter) {
    privacyFilter.addEventListener('change', () => {
      filterTable(searchInput.value)
    })
  }

  if (reportsStatusFilter) {
    reportsStatusFilter.addEventListener('change', () => {
      filterTable(searchInput.value)
    })
  }

  if (reportsTypeFilter) {
    reportsTypeFilter.addEventListener('change', () => {
      filterTable(searchInput.value)
    })
  }

  if (moderatorFilter) {
    moderatorFilter.addEventListener('change', () => {
      filterTable(searchInput.value)
    })
  }

  const privacies = document.querySelectorAll('[data-route]');
  if (privacies) {
    privacies.forEach(function (element) {
      element.addEventListener('click', function () {
        updatePrivacy(element)
      })
    })
  }

  const editorIds = document.querySelectorAll('[id$="-editor"]')
  const replyBtns = document.querySelectorAll("[data-toggle='reply-form']")
  if (editorIds.length > 0) {

    if (threadPlaceholder) {
      threadPlaceholder.addEventListener('click', function () {
        threadPlaceholder.classList.add('hidden')
        threadEditor.classList.remove('hidden')
        document.getElementById('editor-thread-input').focus()
      })

      replyBtns.forEach(btn => {
        btn.addEventListener('click', event => {
          const target = btn.getAttribute('data-target')
          const id = target.split('-')[0]

          if (target == 'thread') {
            threadPlaceholder.classList.add('hidden')
            threadEditor.classList.remove('hidden')
            document.getElementById('editor-thread-input').focus()

          }
          else {
            const targetElement = document.getElementById(target)

            if (targetElement.classList.contains('hidden')) {
              targetElement.classList.add("block")
              targetElement.classList.remove("hidden")
              document.getElementById(`editor-${id}-input`).focus()

            } else {
              targetElement.classList.remove("block")
              targetElement.classList.add("hidden")
            }
          }

        })
      })
    }

    editorIds.forEach(editor => {
      const id = editor.id.replace('-editor', '')
      setupEditor(id)
    })
  }

  const markdownText = document.querySelectorAll('[data-text="markdown"]')

  if (markdownText) {
    markdownText.forEach(element => {
      element.innerHTML = markdownToHTML(element.textContent.trim())
    })
  }

  if (threadEditor) {
    const postId = document.getElementById('postId').textContent

    const submitNewComment = threadEditor.querySelector('[name="submit-btn"]')
    const newCommentContent = document.getElementById('editor-thread-input')

    submitNewComment.addEventListener('click', (e) => {
      const data = {
        content: newCommentContent.value,
        parent_comment_id: null,
      };

      postComment(data, postId);
    });

    const replies = document.querySelectorAll('div[data-parent-id]')
    replies.forEach((ele, postId) => {
      handleReplySubmission(ele, postId)
    })
  }

  const suspendForm = document.getElementById('suspend-form')
  if (suspendForm) {
    suspendForm.addEventListener('submit', (e) => {
      suspendUser(e)
    })
  }

}

// comments

function handleReplySubmission(node, postId) {
  const id = node.getAttribute('data-id');
  const commentContent = document.getElementById(`editor-${id}-input`);
  const submitBtn = document.getElementById(`${id}-editor`).querySelector("[name='submit-btn']");

  submitBtn.addEventListener('click', () => {
    const data = {
      content: commentContent.value,
      parent_comment_id: id,
    };

    postComment(data, postId)
  });
}

async function postComment(data, postId) {
  try {
    const response = await fetch(`/news/${postId}/comment`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'), // CSRF token
      },
      body: JSON.stringify(data), // Send data as JSON
    });

    if (response.ok) {
      const result = await response.json();
      console.log('Comment successfully posted:', result);
      const commentId = result.comment.id;
      window.location.href = `${window.location.href.split('#')[0]}#c-${commentId}`;
      location.reload(true);
    } else {
      console.error('Failed to post comment:', await response.text());

    }
  } catch (error) {
    console.error('Error while posting comment:', error);

  }
}

// moderator users page
// /users/{user_id}/{community_id}/make_moderator
function toggleModerator(userId, communityId, isChecked) {
  const action = isChecked ? 'make_moderator' : 'remove_moderator';
  const confirmationMessage = isChecked
    ? 'Are you sure you want to grant this user moderator privileges in this community?'
    : 'Are you sure you want to revoke this user\'s moderator privileges in this community?';

  if (confirm(confirmationMessage)) {
    fetch(`/users/${userId}/${communityId}/${action}`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
      },
      body: JSON.stringify({})
    })
      .then(response => {
        if (!response.ok) {
          throw new Error('Failed to update moderator status.');
        }
        return response.json();
      })
      .then(data => {
        alert(data.message);
      })
      .catch(error => {
        alert(error.message);
        document.getElementById(`moderator-checkbox-${userId}`).checked = !isChecked;
      });
  } else {
    document.getElementById(`moderator-checkbox-${userId}`).checked = !isChecked;
  }
}

// markdown editor


function markdownToHTML(markdown) {
  const headingClasses = {
    1: "m-0 text-4xl font-bold",
    2: "m-0 text-3xl font-bold",
    3: "m-0 text-2xl font-bold",
    4: "m-0 text-xl font-bold",
    5: "m-0 text-lg font-bold",
    6: "m-0 text-base font-bold",
  }

  return markdown
    .replace(/^(#{1,6})\s*(.+)$/gm, (_, hashes, content) =>
      `<h${hashes.length} class="${headingClasses[hashes.length]}">${content}</h${hashes.length}>`
    ) // Headings
    .replace(/^>\s*(.+)$/gm,
      `<blockquote class="prose-blockquote border-l-4 border-[#4793AF]/[.50] pl-4 italic text-gray-700">$1</blockquote>`
    ) // Blockquotes
    .replace(/-{3,}/g, '<hr class="my-4 border-[#4793AF]/[.50]"/>')
    .replace(/\[([^\[]+)\]\(([^\)]+)\)/g, '<a href=\'\$2\'>\$1</a>')
    .replace(/\*\*(.+?)\*\*/g, '<strong class="font-semibold">$1</strong>') // Bold
    .replace(/\*(.+?)\*/g, '<em class="italic">$1</em>') // Italics
    .replace(/`(.*?)`/g, '<code class="bg-gray-200 p-1 rounded text-[#4793AF]">$1</code>') // Inline Code
    .replace(/^(?!<(h[1-6]|blockquote|hr)[^>]*>).+/gm, '$&<br>')
    .replace(/(<br>\s*){2,}/g, '<br>') // Add <br> for plain text lines
}

function setupEditor(id) {
  const editor = document.getElementById(`${id}-editor`)
  const textarea = document.getElementById(`editor-${id}-input`)
  const preview = document.getElementById(`editor-${id}-preview`)

  textarea.addEventListener("keydown", (e) => {
    if (e.key === "Tab") {
      e.preventDefault()

      const start = textarea.selectionStart
      const end = textarea.selectionEnd

      textarea.value = textarea.value.substring(0, start) + "  " + textarea.value.substring(end)

      textarea.selectionStart = textarea.selectionEnd = start + 2
    }
  })

  textarea.addEventListener('input', (e) => {
    const markdownText = e.target.value
    preview.innerHTML = markdownToHTML(markdownText)
  })

  document.getElementById(`editor-write-toggle-${id}`).addEventListener('change', () => {
    textarea.classList.remove('hidden')
    preview.classList.add('hidden')
  })

  document.getElementById(`editor-preview-toggle-${id}`).addEventListener('change', () => {
    textarea.classList.add('hidden')
    preview.classList.remove('hidden')
  })

  const cancelBtn = editor.querySelector('[name="cancel-btn"]')
  if (cancelBtn) {
    cancelBtn.addEventListener('click', () => {
      editor.classList.add('hidden')

      if (id === 'thread') {
        document.getElementById('thread-placeholder').classList.remove('hidden')
      }

      preview.innerHTML = ''
      textarea.value = ''
    })
  }

}


// admin hubs page

function updatePrivacy(element) {
  const routeID = element.getAttribute('data-route')

  fetch(`/hub/${routeID}/privacy`, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
    }
  })
    .then(response => {
      if (!response.ok) {
        throw new Error(`HTTP error. Status: ${response.status}`)
      }
      return response.json()
    })
    .then(data => {
      if (data.success) {
        if (data.privacy == 'Private') {
          element.innerHTML = `
          <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none"
          stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
          <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
          </svg>
          Private`
          element.classList = 'cursor-pointer inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium rounded-full bg-red-100 text-red-700'
        } else if (data.privacy == 'Public') {
          element.innerHTML = `
          <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none"
          stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
          <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
          <path d="M7 11h10"></path>
          </svg>
          Public
          `
          element.classList = 'cursor-pointer inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium rounded-full bg-green-100 text-green-700'
        }
      }
    })
    .catch(error => {
      console.error('Error updating privacy', error)
    });

}

// admin reports page

function openResolveModal(reportId) {
  const modal = document.getElementById('resolveModal');
  if (modal) {
    modal.classList.remove('hidden');
    const form = modal.querySelector('#resolveForm');
    form.action = `/report/${reportId}/resolve`;
    document.getElementById('report_id').value = reportId;
  } else {
    console.error(`Modal not found!`);
  }
}

function closeResolveModal() {
  const modal = document.getElementById('resolveModal');
  if (modal) {
    modal.classList.add('hidden');
  }
}

// admin topics page

async function acceptTopic(id) {
  fetch(`/topic/${id}/accept`, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
    },
  })
    .then(response => response.json())
    .then(data => {
      if (data.status === 'ok') {
        console.log('Topic accepted successfully');
        const label = document.querySelector(`label[for="status-${id}"`)
        const input = document.getElementById(`status-${id}`)
        input.checked = false
        label.innerHTML =
          `
      <span class="text-green-600 bg-green-100 text-sm border rounded-full px-3 py-1 font-bold">
          Approved
      </span>
      `
      } else {
        console.error('Failed to accept the topic');
      }
    })
    .catch(error => {
      console.error('Error:', error);
    });
}

async function rejectTopic(id) {
  fetch(`/topic/${id}/reject`, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
    }
  })
    .then(response => response.json())
    .then(data => {
      if (data.status === 'ok') {
        console.log('Topic rejected successfully');
        const label = document.querySelector(`label[for="status-${id}"`)
        const input = document.getElementById(`status-${id}`)
        input.checked = false
        label.innerHTML =
          `
      <span class="text-red-600 bg-red-100 text-sm border rounded-full px-3 py-1 font-bold">
          Rejected
      </span>
      `

      } else {
        console.error('Failed to reject the topic')
      }
    })
    .catch(error => {
      console.error('Error:', error)
    });
}

// tables filtering (admin + mod pages)

function updateHeaderText(index, direction) {
  headers.forEach(function (header) {
    header.textContent = header.textContent.replace(/ (ASC|DESC)$/, '');
  });

  const header = headers[index];

  if (direction === 'asc') {
    header.textContent = header.textContent.replace(/ (ASC|DESC)$/, '') + ' ASC';
  } else {
    header.textContent = header.textContent.replace(/ (ASC|DESC)$/, '') + ' DESC';
  }
}

function sortColumn(index) {
  const direction = directions[index] || 'asc';
  const multiplier = direction === 'asc' ? 1 : -1;

  const newRows = Array.from(rows);
  newRows.sort(function (rowA, rowB) {
    const cellA = rowA.querySelectorAll('td')[index].innerHTML
    const cellB = rowB.querySelectorAll('td')[index].innerHTML

    const a = transform(index, cellA)
    const b = transform(index, cellB)

    if (a > b) return 1 * multiplier
    if (a < b) return -1 * multiplier
    return 0
  });

  [].forEach.call(rows, function (row) {
    tableBody.removeChild(row);
  })

  directions[index] = direction === 'asc' ? 'desc' : 'asc';

  newRows.forEach(function (newRow) {
    tableBody.appendChild(newRow);
  })

  updateHeaderText(index, directions[index]);
}



function filterTable(query) {
  const queryLower = query.toLowerCase();

  rows.forEach(function (row) {
    let rowVisible = false;

    row.querySelectorAll('td').forEach(function (cell, index) {
      const dataType = headers[index].getAttribute('data-type');
      if (dataType) {
        const cellText = cell.textContent.toLowerCase();
        if (cellText.includes(queryLower)) {
          rowVisible = true;
        }
      }
    });

    if (statusFilter && statusFilter.value != '' && !row.textContent.includes(statusFilter.value)) {
      rowVisible = false
    }

    if (suspensionFilter && suspensionFilter.value != '') {
      if (row.querySelector('.suspend-btn').classList.contains('hidden') && suspensionFilter.value == 'Suspend') {
        rowVisible = false
      } else if (row.querySelector('.unsuspend-btn').classList.contains('hidden') && suspensionFilter.value == 'Unsuspend') {
        rowVisible = false
      }
    }

    if (adminFilter && adminFilter.value != '') {
      const adminCheck = row.querySelector("td[data-admin] > input")
      if (adminFilter.value == 'admin' && !adminCheck.checked) {
        rowVisible = false
      } else if (adminFilter.value == 'normal' && adminCheck.checked) {
        rowVisible = false
      }
    }

    if (privacyFilter && privacyFilter.value != '' && !row.textContent.includes(privacyFilter.value)) {
      rowVisible = false
    }

    if (reportsStatusFilter && reportsStatusFilter.value != '' && !row.textContent.includes(reportsStatusFilter.value)) {
      rowVisible = false
    }

    if (reportsTypeFilter && reportsTypeFilter.value != '' && !row.textContent.includes(reportsTypeFilter.value)) {
      rowVisible = false
    }

    if (moderatorFilter && moderatorFilter.value != '') {
      const modCheck = row.querySelector("td[data-moderator] > input")
      if (moderatorFilter.value == 'moderator' && !modCheck.checked) {
        rowVisible = false
      } else if (moderatorFilter.value == 'normal' && modCheck.checked) {
        rowVisible = false
      }
    }

    // if (!categoryFilter.value == 'Status' && !row.textContent.toLowerCase().includes(categoryFilter.value.toLowerCase())) {
    //   rowVisible = false
    // }

    row.style.display = rowVisible ? '' : 'none';
  });
}

async function commentVoteUpdate(e) {
  const commentId = e.target.id.split("-")[0];
  const voteType = e.target.id.includes("upvote") ? "upvote" : "downvote";
  const otherVote = document.getElementById(`${commentId}-${voteType == "upvote" ? "downvote" : "upvote"}-c`)
  console.log(otherVote)
  console.log(e.target)

  try {
    const response = await fetch(`/comment/${commentId}/voteupdate`, {
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

      const scoreElement = document.getElementById(`${commentId}-score-c`);
      if (scoreElement) {
        scoreElement.textContent = data.newScore
        // let newScore = data.newScore;
        // if (!scoreElement.textContent.includes("k")) {
        //   let currentScore = parseInt(scoreElement.textContent.replace(/[^\d.-]/g, ''));
        //   newScore = currentScore + newScore;
        //   console.log(newScore)
        //   scoreElement.textContent = newScore >= 1000 ? `${(newScore / 1000).toFixed(1)}k` : newScore;
        // }
      }

      if (data.vote === voteType) {
        e.target.checked = true
        otherVote.checked = false // exclusive
      } else {
        e.target.checked = false
      }

    } else {
      console.error("Failed to update the vote:", await response.text());
    }
  } catch (error) {
    console.error("Error while updating the vote:", error);
  }
}

// admin suspend user

function openSuspendModal(userId) {
  document.getElementById('authenticated_user_id').value = userId;
  document.getElementById('suspend-modal').classList.remove('hidden');
}

function closeSuspendModal() {
  document.getElementById('suspend-modal').classList.add('hidden');
}

async function suspendUser(e) {
  e.preventDefault();

  const form = e.target;
  const authenticatedUserId = form.authenticated_user_id.value;
  const reason = form.reason.value;
  const duration = form.duration.value;

  try {
    const response = await fetch(`/users/${authenticatedUserId}/suspend`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
      },
      body: JSON.stringify({ reason, duration }),
    });

    if (!response.ok) {
      const errorText = await response.text();
      throw new Error(errorText || 'Failed to suspend user');
    }

    const data = await response.json();


    closeSuspendModal();

    updateButtonState(authenticatedUserId, true);
  } catch (error) {
    console.error('Failed to suspend user:', error.message);
    alert('Error: ' + error.message);
  }
}

function updateButtonState(userId, isSuspended) {
  const suspendButton = document.querySelector(`button.suspend-btn[data-user-id="${userId}"]`);
  const unsuspendButton = document.querySelector(`button.unsuspend-btn[data-user-id="${userId}"]`);

  if (isSuspended) {
    if (suspendButton) suspendButton.classList.add('hidden');
    if (unsuspendButton) unsuspendButton.classList.remove('hidden');
  } else {
    if (suspendButton) suspendButton.classList.remove('hidden');
    if (unsuspendButton) unsuspendButton.classList.add('hidden');
  }
}


function unsuspendUser(userId) {
  if (confirm('Are you sure you want to unsuspend this user?')) {
    fetch(`/users/${userId}/unsuspend`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
      },
    })
      .then(response => {
        if (!response.ok) {
          throw new Error('Failed to unsuspend user');
        }
        return response.json();
      })
      .then(data => {
        updateButtonState(userId, false);
      })
      .catch(error => {
        alert('Error unsuspending user: ' + error.message);
      });
  }
}




// function toggleSuspend(userId, isChecked) {
//   const action = isChecked ? 'suspend' : 'unsuspend';
//   const confirmationMessage = isChecked
//     ? 'Are you sure you want to suspend this user?'
//     : 'Are you sure you want to unsuspend this user?';

//   if (confirm(confirmationMessage)) {
//     fetch(`/users/${userId}/${action}`, {
//       method: 'POST',
//       headers: {
//         'Content-Type': 'application/json',
//         'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
//       },
//       body: JSON.stringify({})
//     })
//       .then(response => {
//         if (!response.ok) {
//           throw new Error('Failed to update user status.');
//         }
//         return response.json();
//       })
//       .then(data => {
//         alert(data.message);
//       })
//       .catch(error => {
//         alert(error.message);
//         document.getElementById(`suspend-checkbox-${userId}`).checked = !isChecked;
//       });
//   } else {
//     document.getElementById(`suspend-checkbox-${userId}`).checked = !isChecked;
//   }
// }

async function toggleAdmin(userId, isChecked) {
  const action = isChecked ? 'make_admin' : 'remove_admin';
  const confirmationMessage = isChecked
    ? 'Are you sure you want to grant this user admin privileges?'
    : 'Are you sure you want to revoke this user\'s admin privileges?';

  if (confirm(confirmationMessage)) {
    fetch(`/users/${userId}/${action}`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
      },
      body: JSON.stringify({})
    })
      .then(response => {
        if (!response.ok) {
          throw new Error('Failed to update admin status.');
        }
        return response.json();
      })
      .then(data => {
        alert(data.message);
      })
      .catch(error => {
        alert(error.message);
        document.getElementById(`admin-checkbox-${userId}`).checked = !isChecked;
      });
  } else {
    document.getElementById(`admin-checkbox-${userId}`).checked = !isChecked;
  }
}

async function voteUpdate(e) {
  const postId = e.target.id.split("-")[0];
  const voteType = e.target.id.includes("upvote") ? "upvote" : "downvote";
  const otherVote = document.getElementById(`${postId}-${voteType == "upvote" ? "downvote" : "upvote"}`)
  console.log(otherVote)
  console.log(e.target)

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
        e.target.checked = true
        otherVote.checked = false // exclusive
      } else {
        e.target.checked = false
      }

    } else {
      console.error("Failed to update the vote:", await response.text());
    }
  } catch (error) {
    console.error("Error while updating the vote:", error);
  }
}

function shortNewsUrl() {
  const regex = /^(?:https?:\/\/)?(?:[^@\/\n]+@)?(?:www\.)?([^:\/?\n]+)/;

  const newsUrls = document.querySelectorAll('[data-content="news-url"]');

  newsUrls.forEach(element => {
    const url = element.textContent.trim();
    const match = url.match(regex);
    if (match && match[1]) {
      element.textContent = `( ${match[1]} \u{1F855} )`;
    }
  });
}

function toggleDropdown() {
  const menuButton = document.getElementById('menu-button');
  const dropdownMenu = document.querySelector('[aria-labelledby="menu-button"]');

  // Initially set the menu to hidden state
  dropdownMenu.classList.add('opacity-0', 'scale-95', 'hidden');

  menuButton.addEventListener('click', () => {
    dropdownMenu.classList.remove('opacity-0');
    if (dropdownMenu.classList.contains('dropdown-menu-expand') || dropdownMenu.classList.contains('hidden')) {
      dropdownMenu.classList.remove('dropdown-menu-expand', 'hidden');
      dropdownMenu.classList.add('dropdown-menu-expanded');
    } else {
      // Hide the dropdown
      dropdownMenu.classList.remove('dropdown-menu-expanded');
      dropdownMenu.classList.add('dropdown-menu-expand',);
    }
  });
}

function encodeForAjax(data) {
  if (data == null) return null;
  return Object.keys(data).map(function (k) {
    return encodeURIComponent(k) + '=' + encodeURIComponent(data[k])
  }).join('&');
}

function sendAjaxRequest(method, url, data, handler) {
  let request = new XMLHttpRequest();

  request.open(method, url, true);
  request.setRequestHeader('X-CSRF-TOKEN', document.querySelector('meta[name="csrf-token"]').content);
  request.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
  request.addEventListener('load', handler);
  request.send(encodeForAjax(data));
}

function sendItemUpdateRequest() {
  let item = this.closest('li.item');
  let id = item.getAttribute('data-id');
  let checked = item.querySelector('input[type=checkbox]').checked;

  sendAjaxRequest('post', '/api/item/' + id, { done: checked }, itemUpdatedHandler);
}

function sendDeleteItemRequest() {
  let id = this.closest('li.item').getAttribute('data-id');

  sendAjaxRequest('delete', '/api/item/' + id, null, itemDeletedHandler);
}

function sendCreateItemRequest(event) {
  let id = this.closest('article').getAttribute('data-id');
  let description = this.querySelector('input[name=description]').value;

  if (description != '')
    sendAjaxRequest('put', '/api/cards/' + id, { description: description }, itemAddedHandler);

  event.preventDefault();
}

function sendDeleteCardRequest(event) {
  let id = this.closest('article').getAttribute('data-id');

  sendAjaxRequest('delete', '/api/cards/' + id, null, cardDeletedHandler);
}

function sendCreateCardRequest(event) {
  let name = this.querySelector('input[name=name]').value;

  if (name != '')
    sendAjaxRequest('put', '/api/cards/', { name: name }, cardAddedHandler);

  event.preventDefault();
}

function itemUpdatedHandler() {
  let item = JSON.parse(this.responseText);
  let element = document.querySelector('li.item[data-id="' + item.id + '"]');
  let input = element.querySelector('input[type=checkbox]');
  element.checked = item.done == "true";
}

function itemAddedHandler() {
  if (this.status != 200) window.location = '/';
  let item = JSON.parse(this.responseText);

  // Create the new item
  let new_item = createItem(item);

  // Insert the new item
  let card = document.querySelector('article.card[data-id="' + item.card_id + '"]');
  let form = card.querySelector('form.new_item');
  form.previousElementSibling.append(new_item);

  // Reset the new item form
  form.querySelector('[type=text]').value = "";
}

function itemDeletedHandler() {
  if (this.status != 200) window.location = '/';
  let item = JSON.parse(this.responseText);
  let element = document.querySelector('li.item[data-id="' + item.id + '"]');
  element.remove();
}

function cardDeletedHandler() {
  if (this.status != 200) window.location = '/';
  let card = JSON.parse(this.responseText);
  let article = document.querySelector('article.card[data-id="' + card.id + '"]');
  article.remove();
}

function cardAddedHandler() {
  if (this.status != 200) window.location = '/';
  let card = JSON.parse(this.responseText);

  // Create the new card
  let new_card = createCard(card);

  // Reset the new card input
  let form = document.querySelector('article.card form.new_card');
  form.querySelector('[type=text]').value = "";

  // Insert the new card
  let article = form.parentElement;
  let section = article.parentElement;
  section.insertBefore(new_card, article);

  // Focus on adding an item to the new card
  new_card.querySelector('[type=text]').focus();
}

function createCard(card) {
  let new_card = document.createElement('article');
  new_card.classList.add('card');
  new_card.setAttribute('data-id', card.id);
  new_card.innerHTML = `
  
    <header>
      <h2><a href="cards/${card.id}">${card.name}</a></h2>
      <a href="#" class="delete">&#10761;</a>
    </header>
    <ul></ul>
    <form class="new_item">
      <input name="description" type="text">
    </form>`;

  let creator = new_card.querySelector('form.new_item');
  creator.addEventListener('submit', sendCreateItemRequest);

  let deleter = new_card.querySelector('header a.delete');
  deleter.addEventListener('click', sendDeleteCardRequest);

  return new_card;
}

function createItem(item) {
  let new_item = document.createElement('li');
  new_item.classList.add('item');
  new_item.setAttribute('data-id', item.id);
  new_item.innerHTML = `
    <label>
      <input type="checkbox"> <span>${item.description}</span><a href="#" class="delete">&#10761;</a>
    </label>
    `;

  new_item.querySelector('input').addEventListener('change', sendItemUpdateRequest);
  new_item.querySelector('a.delete').addEventListener('click', sendDeleteItemRequest);

  return new_item;
}

addEventListeners()
shortNewsUrl()

// DELETE COMMUNITY

document.addEventListener('DOMContentLoaded', function () {
  const deleteButtons = document.querySelectorAll('.delete-button-hub');

  deleteButtons.forEach(button => {
    button.closest('form').addEventListener('submit', async function (e) {
      e.preventDefault();

      if (confirm('Are you sure you want to delete this community?')) {
        try {
          const response = await fetch(this.action, {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
            },
            body: JSON.stringify({
              _method: 'DELETE'
            })
          });

          const result = await response.json();

          const notification = document.createElement('div');
          notification.className = 'fixed left-1/2 top-4 -translate-x-1/2 w-96 p-4 rounded shadow-lg';

          if (response.ok) {
            notification.style.backgroundColor = '#c5e6a6';
            notification.style.border = '2px solid #34a853';
          } else {
            notification.style.backgroundColor = '#ed6a5a';
            notification.style.border = '2px solid #a30000';
          }

          const icon = document.createElement('span');
          icon.className = 'inline-block mr-2';
          icon.innerHTML = response.ok
            ? '✓'
            : '✕';
          icon.style.color = response.ok ? '#34a853' : '#a30000';

          const messageText = document.createElement('span');
          messageText.textContent = response.ok
            ? 'Community successfully deleted!'
            : 'Failed to delete community. It may contain posts or you may not have permission.';
          messageText.style.color = '#333333';

          notification.appendChild(icon);
          notification.appendChild(messageText);

          document.body.appendChild(notification);

          setTimeout(() => {
            notification.remove();
            if (response.ok) {
              window.location.href = '/admin/hubs';
            }
          }, 3000);

        } catch (error) {
          console.error('Error:', error);

          const errorNotification = document.createElement('div');
          errorNotification.className = 'fixed left-1/2 top-4 -translate-x-1/2 w-96 p-4 rounded shadow-lg';
          errorNotification.style.backgroundColor = '#ffebee';
          errorNotification.style.border = '1px solid #a30000';

          const errorIcon = document.createElement('span');
          errorIcon.className = 'inline-block mr-2';
          errorIcon.innerHTML = '✕';
          errorIcon.style.color = '#a30000';

          const errorText = document.createElement('span');
          errorText.textContent = 'An error occurred while processing your request.';
          errorText.style.color = '#333333';

          errorNotification.appendChild(errorIcon);
          errorNotification.appendChild(errorText);
          document.body.appendChild(errorNotification);

          setTimeout(() => {
            errorNotification.remove();
          }, 3000);
        }
      }
    });
  });
});

// follow request

function handleFollowRequest(url, notificationId, action) {
  if (!confirm(`Are you sure you want to ${action} this follow request?`)) {
    return;
  }

  fetch(url, {
    method: 'POST',
    headers: {
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
      'Accept': 'application/json',
    },
  })
    .then(response => {
      if (response.ok) {
        document.querySelector(`[data-notification-id="${notificationId}"]`).remove();
        alert(`Follow request ${action}ed successfully.`);
      }
    })
    .catch(error => {
      alert(`Error: ${error.message}`);
    });
}

// delete comment

document.addEventListener('DOMContentLoaded', function () {
  const deleteCommentButtons = document.querySelectorAll('.delete-comment-button');

  deleteCommentButtons.forEach(button => {
    button.closest('form').addEventListener('submit', async function (e) {
      e.preventDefault();

      if (confirm('Are you sure you want to delete this comment?')) {
        try {
          const response = await fetch(this.action, {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
            },
            body: JSON.stringify({
              _method: 'PUT'
            })
          });

          const result = await response.json();

          const notification = document.createElement('div');
          notification.className = 'fixed left-1/2 top-16 -translate-x-1/2 w-96 p-4 rounded shadow-lg';

          if (result.success) {
            notification.style.backgroundColor = '#c5e6a6';
            notification.style.border = '2px solid #34a853';
            notification.innerHTML = `<span class="inline-block mr-2" style="color: #34a853;">✓</span> ${result.message}`;
          } else {
            notification.style.backgroundColor = '#ed6a5a';
            notification.style.border = '2px solid #a30000';
            notification.innerHTML = `<span class="inline-block mr-2" style="color: #a30000;">✕</span> ${result.message}`;
          }

          document.body.appendChild(notification);

          setTimeout(() => {
            notification.remove();
            if (result.success) {
              button.closest('.comment-container').remove();
            }
          }, 3000);

        } catch (error) {
          console.error('Error:', error);

          const errorNotification = document.createElement('div');
          errorNotification.className = 'fixed left-1/2 top-4 -translate-x-1/2 w-96 p-4 rounded shadow-lg';
          errorNotification.style.backgroundColor = '#ffebee';
          errorNotification.style.border = '1px solid #a30000';
          errorNotification.innerHTML = `<span class="inline-block mr-2" style="color: #a30000;">✕</span> An error occurred while processing your request.`;

          document.body.appendChild(errorNotification);

          setTimeout(() => {
            errorNotification.remove();
          }, 3000);
        }
      }
    });
  });
});

// create hub

const nameInput = document.getElementById('name');
const descriptionInput = document.getElementById('description');
const previewName = document.getElementById('preview-name');
const previewDescription = document.getElementById('preview-description');
const previewImage = document.getElementById('preview-image');
const previewMembers = document.getElementById('preview-members');
const previewOnline = document.getElementById('preview-online');
const privacyIndicator = document.getElementById('privacy-indicator');

function createLockSVG(isPrivate) {
  const svg = document.createElementNS("http://www.w3.org/2000/svg", "svg");
  svg.setAttribute("xmlns", "http://www.w3.org/2000/svg");
  svg.setAttribute("viewBox", "0 0 24 24");
  svg.setAttribute("width", "24");
  svg.setAttribute("height", "24");
  svg.setAttribute("fill", isPrivate ? "red" : "green");
  svg.setAttribute("class", "ml-2");

  const path = document.createElementNS("http://www.w3.org/2000/svg", "path");

  if (isPrivate) {
    path.setAttribute("d", "M17 10V7a5 5 0 0 0-5-5h-2a5 5 0 0 0-5 5v3a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-6a2 2 0 0 0-2-2zM7 7a3 3 0 0 1 3-3h2a3 3 0 0 1 3 3v3H7V7z");
    path.style.fill = '#EF4444';
  } else {
    path.setAttribute("d", "M17 8V7a5 5 0 0 0-5-5h-2a5 5 0 0 0-5 5v1a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-6a2 2 0 0 0-2-2zm-9-1a3 3 0 0 1 3-3h2a3 3 0 0 1 3 3v1H8V7z");
    path.style.fill = '#22C55E';
  }

  svg.appendChild(path);
  return svg;
}

function updatePrivacyIndicator() {
  privacyIndicator.innerHTML = '';

  const selectedPrivacy = document.querySelector('input[name="privacy"]:checked').value;
  const lockSVG = createLockSVG(selectedPrivacy === 'private');
  privacyIndicator.appendChild(lockSVG);
}

if (privacyIndicator) {
  updatePrivacyIndicator()
}

const privacyRadios = document.querySelectorAll('input[name="privacy"]')
if (privacyRadios) {
  privacyRadios.forEach(radio => {
    radio.addEventListener('change', updatePrivacyIndicator);
  });
}


if (nameInput) {
  nameInput.addEventListener('input', () => {
    previewName.textContent = nameInput.value;
  });
}

if (descriptionInput) {
  descriptionInput.addEventListener('input', () => {
    previewDescription.textContent = descriptionInput.value;
  });
}

const imageInput = document.getElementById('image');
if (imageInput) {
  imageInput.addEventListener('change', () => {
    const file = imageInput.files[0];
    previewImage.src = URL.createObjectURL(file);
  });
}

if (previewMembers && previewOnline) {
  previewMembers.textContent = '1 member'
  previewOnline.textContent = '1 online'
}




const moderatorsSelect = document.getElementById('moderators');
if (moderatorsSelect) {
  moderatorsSelect.multiple = true
}


document.addEventListener('DOMContentLoaded', () => {
  const moderatorsSelect = document.getElementById('moderators');
  const selectedModeratorsContainer = document.getElementById('selected-moderators');
  const previewMembers = document.getElementById('preview-members');

  // Color palette inspired by news and journalism themes
  const newsColors = [
    { bg: '#2C3E50', text: '#ECF0F1' },  // Dark blue-gray with light text
    { bg: '#34495E', text: '#ECF0F1' },  // Slightly lighter blue-gray
    { bg: '#2980B9', text: '#FFFFFF' },  // Bright blue
    { bg: '#3498DB', text: '#FFFFFF' },  // Lighter bright blue
    { bg: '#16A085', text: '#FFFFFF' },  // Teal green
    { bg: '#1ABC9C', text: '#FFFFFF' },  // Lighter teal
    { bg: '#8E44AD', text: '#FFFFFF' },  // Deep purple
    { bg: '#9B59B6', text: '#FFFFFF' }   // Lighter purple
  ];
  let colorIndex = 0;

  if (moderatorsSelect) {
    moderatorsSelect.addEventListener('change', () => {
      selectedModeratorsContainer.innerHTML = '';

      Array.from(moderatorsSelect.selectedOptions).forEach(option => {
        // Cycle through colors
        const currentColor = newsColors[colorIndex];
        colorIndex = (colorIndex + 1) % newsColors.length;

        // Create moderator chip with enhanced styling
        const moderatorChip = document.createElement('div');
        moderatorChip.className = 'moderator-chip';
        moderatorChip.style.backgroundColor = currentColor.bg;
        moderatorChip.style.color = currentColor.text;

        // User Icon (SVG)
        const iconSvg = document.createElementNS("http://www.w3.org/2000/svg", "svg");
        iconSvg.setAttribute('viewBox', '0 0 24 24');
        iconSvg.setAttribute('width', '24');
        iconSvg.setAttribute('height', '24');
        iconSvg.setAttribute('fill', 'none');
        iconSvg.setAttribute('stroke', currentColor.text);
        iconSvg.setAttribute('stroke-width', '2');
        iconSvg.setAttribute('stroke-linecap', 'round');
        iconSvg.setAttribute('stroke-linejoin', 'round');
        iconSvg.className = 'moderator-chip-icon';

        // User icon path
        const iconPath = document.createElementNS("http://www.w3.org/2000/svg", "path");
        iconPath.setAttribute('d', 'M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2');
        const iconCircle = document.createElementNS("http://www.w3.org/2000/svg", "circle");
        iconCircle.setAttribute('cx', '12');
        iconCircle.setAttribute('cy', '7');
        iconCircle.setAttribute('r', '4');

        iconSvg.appendChild(iconPath);
        iconSvg.appendChild(iconCircle);

        // Moderator Name
        const text = document.createElement('span');
        text.textContent = option.text;
        text.className = 'moderator-chip-name';

        // Remove Button with icon
        const removeBtn = document.createElement('button');
        removeBtn.innerHTML = '&times;';
        removeBtn.className = 'moderator-chip-remove';

        // Hover and interaction effects
        moderatorChip.addEventListener('mouseover', () => {
          moderatorChip.style.transform = 'scale(1.05)';
          moderatorChip.style.boxShadow = '0 4px 8px rgba(0,0,0,0.2)';
        });

        moderatorChip.addEventListener('mouseout', () => {
          moderatorChip.style.transform = 'scale(1)';
          moderatorChip.style.boxShadow = 'none';
        });

        removeBtn.addEventListener('click', () => {
          option.selected = false;
          moderatorChip.remove();
          updateMembersCount();
        });

        // Append elements
        moderatorChip.appendChild(iconSvg);
        moderatorChip.appendChild(text);
        moderatorChip.appendChild(removeBtn);
        selectedModeratorsContainer.appendChild(moderatorChip);
      });

      updateMembersCount();
    });
  }


  function updateMembersCount() {
    const count = moderatorsSelect.selectedOptions.length + 1; // Includes creator
    previewMembers.textContent = `${count} member${count > 1 ? 's' : ''}`;
  }

  // create post
  const newsRadio = document.getElementById('postTypeNews');
  const topicRadio = document.getElementById('postTypeTopic');
  const newsUrlContainer = document.getElementById('newsUrlContainer');
  const titleInput = document.getElementById('title');

  function toggleNewsUrl() {
    if (newsUrlContainer && newsRadio) {
      newsUrlContainer.style.display = newsRadio.checked ? 'block' : 'none';
    }
  }

  function toggleTitleRequired() {
    if (titleInput && topicRadio) {
      titleInput.required = topicRadio.checked;
    }
  }

  toggleNewsUrl();
  toggleTitleRequired();

  if (newsRadio && topicRadio) {
    newsRadio.addEventListener('change', toggleNewsUrl);
    topicRadio.addEventListener('change', toggleNewsUrl);
    newsRadio.addEventListener('change', toggleTitleRequired);
    topicRadio.addEventListener('change', toggleTitleRequired);
  }



  const authorsSelect = document.getElementById('authors');
  const selectedAuthorsContainer = document.getElementById('selected-authors');

  // Color palette for author chips
  const authorColors = [
    { bg: '#1D3557', text: '#F1FAEE' },  // Navy Blue
    { bg: '#457B9D', text: '#F1FAEE' },  // Steel Blue
    { bg: '#A8DADC', text: '#1D3557' },  // Aqua
    { bg: '#E63946', text: '#F1FAEE' },  // Red
    { bg: '#F4A261', text: '#264653' },  // Coral
    { bg: '#2A9D8F', text: '#E9C46A' },  // Teal
  ];

  let colorIndexAuthors = 0;
  if (authorsSelect) {
    authorsSelect.addEventListener('change', () => {
      selectedAuthorsContainer.innerHTML = '';

      Array.from(authorsSelect.selectedOptions).forEach(option => {
        const currentColor = authorColors[colorIndexAuthors];
        colorIndexAuthors = (colorIndexAuthors + 1) % authorColors.length;

        const authorChip = document.createElement('div');
        authorChip.className = 'author-chip';
        authorChip.style.backgroundColor = currentColor.bg;
        authorChip.style.color = currentColor.text;

        const iconSvg = document.createElementNS("http://www.w3.org/2000/svg", "svg");
        iconSvg.setAttribute('viewBox', '0 0 24 24');
        iconSvg.setAttribute('width', '24');
        iconSvg.setAttribute('height', '24');
        iconSvg.setAttribute('fill', 'none');
        iconSvg.setAttribute('stroke', currentColor.text);
        iconSvg.setAttribute('stroke-width', '2');
        iconSvg.setAttribute('stroke-linecap', 'round');
        iconSvg.setAttribute('stroke-linejoin', 'round');
        iconSvg.className = 'author-chip-icon';

        const iconPath = document.createElementNS("http://www.w3.org/2000/svg", "path");
        iconPath.setAttribute('d', 'M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2');
        const iconCircle = document.createElementNS("http://www.w3.org/2000/svg", "circle");
        iconCircle.setAttribute('cx', '12');
        iconCircle.setAttribute('cy', '7');
        iconCircle.setAttribute('r', '4');

        iconSvg.appendChild(iconPath);
        iconSvg.appendChild(iconCircle);

        const text = document.createElement('span');
        text.textContent = option.text;
        text.className = 'author-chip-name';

        const removeBtn = document.createElement('button');
        removeBtn.innerHTML = '&times;';
        removeBtn.className = 'author-chip-remove';

        authorChip.addEventListener('mouseover', () => {
          authorChip.style.transform = 'scale(1.05)';
          authorChip.style.boxShadow = '0 4px 8px rgba(0,0,0,0.2)';
        });
        authorChip.addEventListener('mouseout', () => {
          authorChip.style.transform = 'scale(1)';
          authorChip.style.boxShadow = 'none';
        });

        removeBtn.addEventListener('click', () => {
          option.selected = false;
          authorChip.remove();
          updateMembersCount();
        });

        authorChip.appendChild(iconSvg);
        authorChip.appendChild(text);
        authorChip.appendChild(removeBtn);
        selectedAuthorsContainer.appendChild(authorChip);
      });

    });
  }


  // Add CSS for chip styling
  const style = document.createElement('style');
  style.textContent = `
  .author-chip {
            display: flex;
            align-items: center;
            border-radius: 9999px;
            overflow: hidden;
            margin: 4px;
            max-width: 250px;
            font-size: 0.875rem;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        .author-chip-icon {
            width: 2rem;
            height: 2rem;
            margin-right: 0.5rem;
            padding: 0.25rem;
        }
        .author-chip-name {
            flex-grow: 1;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            margin-right: 0.5rem;
        }
        .author-chip-remove {
            padding: 0.25rem 0.5rem;
            background: rgba(0,0,0,0.1);
            border: none;
            cursor: pointer;
            transition: background 0.2s ease;
            font-size: 1.2rem;
            line-height: 1;
        }
        .author-chip-remove:hover {
            background: rgba(0,0,0,0.2);
        }
        .moderator-chip {
            display: flex;
            align-items: center;
            border-radius: 9999px;
            overflow: hidden;
            margin: 4px;
            max-width: 250px;
            font-size: 0.875rem;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .moderator-chip-icon {
            width: 2rem;
            height: 2rem;
            margin-right: 0.5rem;
            padding: 0.25rem;
        }

        .moderator-chip-name {
            flex-grow: 1;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            margin-right: 0.5rem;
        }

        .moderator-chip-remove {
            padding: 0.25rem 0.5rem;
            background: rgba(0,0,0,0.1);
            border: none;
            cursor: pointer;
            transition: background 0.2s ease;
            font-size: 1.2rem;
            line-height: 1;
        }

        .moderator-chip-remove:hover {
            background: rgba(0,0,0,0.2);
        }
        `;
  document.head.appendChild(style);
});


// edit pages

document.addEventListener('DOMContentLoaded', function () {
  const postForm = document.getElementById('update-post-form')

  if (postForm) {
    const postId = postForm.getAttribute('data-post-id')
    const authorsContainer = document.querySelector('ul.authors-list');
    const postType = postForm.getAttribute('data-post-type')
    authorsContainer.addEventListener('click', function (event) {
      const removeButton = event.target.closest('.remove-author-btn');
      if (!removeButton) return;

      const authorId = removeButton.getAttribute('data-author-id');
      const authorItem = removeButton.closest('li');

      fetch(`/${postType}/${postId}/remove-authors`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
          authors_to_remove: [authorId]
        })
      })
        .then(response => {
          if (!response.ok) {
            throw new Error('Not possible to remove author');
          }
          return response.json();
        })
        .then(data => {
          // Remove o item da lista
          authorItem.remove();
        })
        .catch(error => {
          alert(error.message);
        });
    });
  }

});


document.addEventListener('DOMContentLoaded', function () {
  const deleteButtons = document.querySelectorAll('.delete-button');
  if (deleteButtons) {
    deleteButtons.forEach(button => {
      button.closest('form').addEventListener('submit', async function (e) {
        e.preventDefault();

        if (confirm('Are you sure you want to delete this post?')) {
          try {
            const response = await fetch(this.action, {
              method: 'POST',
              headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
              },
              body: JSON.stringify({
                _method: 'DELETE'
              })
            });

            const result = await response.json();

            const notification = document.createElement('div');
            notification.className = 'fixed left-1/2 top-16 -translate-x-1/2 w-96 p-4 rounded shadow-lg';

            if (response.ok) {
              notification.style.backgroundColor = '#c5e6a6';
              notification.style.border = '2px solid #34a853';
            } else {
              notification.style.backgroundColor = '#ed6a5a';
              notification.style.border = '2px solid #a30000';
            }

            const icon = document.createElement('span');
            icon.className = 'inline-block mr-2';
            icon.innerHTML = response.ok
              ? '✓'
              : '✕';
            icon.style.color = response.ok ? '#34a853' : '#a30000';

            const messageText = document.createElement('span');
            messageText.textContent = response.ok
              ? 'Post successfully deleted!'
              : result.message || 'Failed to delete post.';
            messageText.style.color = '#333333';

            notification.appendChild(icon);
            notification.appendChild(messageText);

            document.body.appendChild(notification);

            setTimeout(() => {
              notification.remove();
              if (response.ok) {
                window.location.href = '/global';  // Redirect after success
              }
            }, 3000);

          } catch (error) {
            console.error('Error:', error);

            const errorNotification = document.createElement('div');
            errorNotification.className = 'fixed left-1/2 top-4 -translate-x-1/2 w-96 p-4 rounded shadow-lg';
            errorNotification.style.backgroundColor = '#ffebee';
            errorNotification.style.border = '1px solid #a30000';

            const errorIcon = document.createElement('span');
            errorIcon.className = 'inline-block mr-2';
            errorIcon.innerHTML = '✕';
            errorIcon.style.color = '#a30000';

            const errorText = document.createElement('span');
            errorText.textContent = 'An error occurred while processing your request.';
            errorText.style.color = '#333333';

            errorNotification.appendChild(errorIcon);
            errorNotification.appendChild(errorText);
            document.body.appendChild(errorNotification);

            setTimeout(() => {
              errorNotification.remove();
            }, 3000);
          }
        }
      });
    });
  }

});




// layout.js

document.addEventListener('DOMContentLoaded', () => {
  const searchInput = document.getElementById('search-input');
  const searchResults = document.getElementById('search-results');
  let debounceTimer;

  if (searchInput && searchResults) {
    const toggleSearchResults = (show) => {
      searchResults.style.opacity = show ? '1' : '0';
      searchResults.style.transform = show ? 'scale(1)' : 'scale(0.95)';
      searchResults.style.pointerEvents = show ? 'auto' : 'none';
    };

    const createCommunityItem = (community) => `
        <div class="p-3 rounded-lg hover:bg-gray-50 transition-colors duration-200 cursor-pointer" 
             onclick="window.location.href='${community.route}'">
            <div class="flex items-center gap-2">
                <div class="w-2 h-2 rounded-full object-cover bg-green-500"></div>
                <span class="text-sm text-gray-700">${community.name}</span>
            </div>
            ${community.description ? `
                <p class="text-xs text-gray-500 mt-1 ml-4">${community.description}</p>
            ` : ''}
        </div>
    `;

    const createPostItem = (post) => `
        <div class="p-3 rounded-lg hover:bg-gray-50 transition-colors duration-200 cursor-pointer"
             onclick="window.location.href='${post.community_route}'">
            <span class="text-sm text-gray-700">${post.name}</span>
            <div class="text-xs text-gray-500 mt-1">in ${post.community}</div>
            ${post.content ? `
                <p class="text-xs text-gray-500 mt-1 line-clamp-2">${post.content}</p>
            ` : ''}
        </div>
    `;

    const createUserItem = (user) => `
    <div class="p-3 rounded-lg hover:bg-gray-50 transition-colors duration-200 cursor-pointer"
         onclick="window.location.href='${user.route}'">
        <div class="flex items-center gap-3">
            ${user.image ? `
                <img src="${user.image}" class="w-6 h-6 rounded-full object-cover">
            ` : `
                <div class="w-6 h-6 rounded-full bg-blue-500"></div>
            `}
            <span class="text-sm text-gray-700">@${user.name}</span>
        </div>
    </div>
  `;


    const updateSearchResults = (results) => {
      const communitiesContainer = searchResults.querySelector('.from-red-50.to-blue-50 .space-y-2');
      const postsContainer = searchResults.querySelector('.from-blue-50.to-green-50 .space-y-2');
      const usersContainer = searchResults.querySelector('.from-green-50.to-red-50 .space-y-2');

      // Update communities section
      communitiesContainer.innerHTML = results.communities.length
        ? results.communities.map(createCommunityItem).join('')
        : '<div class="p-3 text-sm text-gray-500">No communities found</div>';

      // Update posts section
      postsContainer.innerHTML = results.posts.length
        ? results.posts.map(createPostItem).join('')
        : '<div class="p-3 text-sm text-gray-500">No posts found</div>';

      // Update users section
      usersContainer.innerHTML = results.users.length
        ? results.users.map(createUserItem).join('')
        : '<div class="p-3 text-sm text-gray-500">No users found</div>';

      toggleSearchResults(true);
    };

    const performSearch = async (searchTerm) => {
      if (!searchTerm.trim()) {
        toggleSearchResults(false);
        return;
      }

      try {
        const response = await fetch(`/search?search=${encodeURIComponent(searchTerm)}`, {
          headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
          }
        });

        if (!response.ok) throw new Error('Search request failed');

        const results = await response.json();
        updateSearchResults(results);
      } catch (error) {
        console.error('Search error:', error);
        // Show error state in dropdown
        searchResults.innerHTML = `
                <div class="p-4 text-sm text-red-500">
                    An error occurred while searching. Please try again.
                </div>
            `;
        toggleSearchResults(true);
      }
    };

    searchInput.addEventListener('input', (e) => {
      clearTimeout(debounceTimer);
      debounceTimer = setTimeout(() => performSearch(e.target.value), 300);
    });

    searchInput.addEventListener('focus', () => {
      if (searchInput.value.trim()) {
        toggleSearchResults(true);
      }
    });

    document.addEventListener('click', (e) => {
      if (!searchResults.contains(e.target) && e.target !== searchInput) {
        toggleSearchResults(false);
      }
    });
  }
});

// ---------- Responsive design ----------

document.addEventListener('DOMContentLoaded', function () {
  const leftSidebar = document.getElementById('left-sidebar');
  const mobileMenuButton = document.getElementById('mobile-menu-button');
  let isMenuOpen = false;

  if (leftSidebar && mobileMenuButton) {
    // Function to toggle menu
    function toggleMobileMenu() {
      isMenuOpen = !isMenuOpen;

      if (isMenuOpen) {
        // Open menu
        leftSidebar.classList.remove('-translate-x-full');
        leftSidebar.classList.add('translate-x-0');
        mobileMenuButton.classList.add('menu-open');
        // Add overlay
        createOverlay();
      } else {
        // Close menu
        leftSidebar.classList.remove('translate-x-0');
        leftSidebar.classList.add('-translate-x-full');
        mobileMenuButton.classList.remove('menu-open');
        // Remove overlay
        removeOverlay();
      }
    }

    // Create overlay function
    function createOverlay() {
      const overlay = document.createElement('div');
      overlay.id = 'mobile-menu-overlay';
      overlay.className = 'fixed inset-0 bg-black bg-opacity-50 z-30';
      overlay.addEventListener('click', toggleMobileMenu);
      document.body.appendChild(overlay);
    }

    function removeOverlay() {
      const overlay = document.getElementById('mobile-menu-overlay');
      if (overlay) {
        overlay.remove();
      }
    }

    mobileMenuButton.addEventListener('click', toggleMobileMenu);

    window.addEventListener('resize', () => {
      if (window.innerWidth >= 768 && isMenuOpen) {
        toggleMobileMenu();
      }
    });
  }
});


document.addEventListener('DOMContentLoaded', function () {
  const profileIcon = document.getElementById('profileIcon');
  const dropdownMenu = document.getElementById('dropdownMenu');

  if (profileIcon) {
    // Mostrar o dropdown ao clicar no ícone
    profileIcon.addEventListener('click', function (event) {
      event.preventDefault();
      dropdownMenu.classList.toggle('hidden');
    });

    // Fechar o dropdown ao clicar fora dele
    document.addEventListener('click', function (event) {
      if (!dropdownMenu.contains(event.target) && !profileIcon.contains(event.target)) {
        dropdownMenu.classList.add('hidden');
      }
    });
  }

});

// ---------- Favorite button ----------

function toggleFavorite(postId) {
  const isChecked = document.getElementById(`favorite-${postId}`).checked;
  const url = isChecked ? `/favorite/${postId}/add` : `/favorite/${postId}/remove`;

  fetch(url, {
    method: 'POST',
    headers: {
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
      'Content-Type': 'application/json',
    },
    body: JSON.stringify({ id: postId })
  })
    .then(response => response.json())
    .then(data => {
      if (data.message) {
        console.log(data.message);
      } else {
        console.log('An error occurred');
      }
    })
    .catch(error => console.error('Error:', error));
}


function updateImagePreview(event) {
  const file = event.target.files[0];
  if (file) {
    const reader = new FileReader();
    reader.onload = function (e) {
      const profileImage = document.getElementById('currentProfileImage');
      if (profileImage) {
        profileImage.src = e.target.result;
      }
    };
    reader.readAsDataURL(file);
  }
}

function markAsRead(notificationId) {
  fetch(`/notifications/${notificationId}/read`, {
      method: 'GET',
      headers: {
          'Accept': 'application/json',
      },
  }).then(response => {
      if (response.ok) {
          location.reload(); 
      }
  });
}





