
// Edit User Profile

function updateImagePreview(event) {
    const input = event.target;
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.querySelector('.rounded-full');
            preview.src = e.target.result;
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function submitAndRedirect(form, redirectUrl) {
    form.submit();

    form.addEventListener('submit', function(e) {
        if (!e.defaultPrevented) {
            window.location.href = redirectUrl;
        }
    });
}

function updateImagePreview(event) {
    const file = event.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.querySelector('img') || document.querySelector('.rounded-full div');
            if (preview.tagName === 'IMG') {
                preview.src = e.target.result;
            } else {
                const img = document.createElement('img');
                img.src = e.target.result;
                img.classList.add('h-20', 'w-20', 'rounded-full', 'object-cover');
                preview.parentNode.replaceChild(img, preview);
            }
        }
        reader.readAsDataURL(file);
    }
}
