const searchInput = document.getElementById('search-input');
const table = document.querySelector('table');
const tableBody = table ? table.querySelector('tbody') : null;
const rows = tableBody ? tableBody.querySelectorAll('tr') : null;
const headers = table ? table.querySelectorAll('th') : null

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
    threadPlaceholder.addEventListener('click', function () {
      threadPlaceholder.classList.add('hidden')
      threadEditor.classList.remove('hidden')
    })

    replyBtns.forEach(btn => {
      btn.addEventListener('click', event => {
        const targetId = btn.getAttribute('data-target')
        const targetElement = document.getElementById(targetId)
        console.log(targetElement)
        if (targetElement.classList.contains('hidden')) {
          targetElement.classList.add("block")
          targetElement.classList.remove("hidden")
        } else {
          targetElement.classList.remove("block")
          targetElement.classList.add("hidden")
        }
      })
    })

    editorIds.forEach(editor => {
      const id = editor.id.replace('-editor', '')
      setupEditor(id)
    })
  }

  const markdownText = document.querySelectorAll('[data-text="markdown"]')
  if (markdownText) {
    markdownText.forEach(element => {
      element.innerHTML = markdownToHTML(element.textContent)
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

function toggleModerator(userId, communityId, isChecked) {
  const action = isChecked ? 'make_moderator' : 'remove_moderator';
  const confirmationMessage = isChecked
    ? 'Are you sure you want to grant this user moderator privileges in this community?'
    : 'Are you sure you want to revoke this user\'s moderator privileges in this community?';

  if (confirm(confirmationMessage)) {
    fetch(`/hub/${communityId}/${action}/${userId}`, {
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
    preview.innerHTML = markdownToHTML(markdownText, id)
  })

  document.getElementById(`editor-write-toggle-${id}`).addEventListener('change', () => {
    textarea.classList.remove('hidden')
    preview.classList.add('hidden')
  })

  document.getElementById(`editor-preview-toggle-${id}`).addEventListener('change', () => {
    textarea.classList.add('hidden')
    preview.classList.remove('hidden')
  })

  editor.querySelector('[name="cancel-btn"]').addEventListener('click', () => {
    editor.classList.add('hidden')

    if (id === 'thread') {
      document.getElementById('thread-placeholder').classList.remove('hidden')
    }

    preview.innerHTML = ''
    textarea.value = ''
  })
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

  if (suspendButton) suspendButton.disabled = isSuspended;
  if (unsuspendButton) unsuspendButton.disabled = !isSuspended;

  if (isSuspended) {
    if (suspendButton) suspendButton.style.display = 'none';
    if (unsuspendButton) unsuspendButton.style.display = 'inline-block';
  } else {
    if (suspendButton) suspendButton.style.display = 'inline-block';
    if (unsuspendButton) unsuspendButton.style.display = 'none';
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

document.getElementById('suspend-form').addEventListener('submit', suspendUser);


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

document.addEventListener('DOMContentLoaded', function() {
  const deleteButtons = document.querySelectorAll('.delete-button-hub');
  
  deleteButtons.forEach(button => {
      button.closest('form').addEventListener('submit', async function(e) {
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

// FOLLOW REQUEST

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
      } else {
          return response.json().then(error => {
              throw new Error(error.message || 'Failed to process the request.');
          });
      }
  })
  .catch(error => {
      alert(`Error: ${error.message}`);
  });
  }

// DELETE COMMENT

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


