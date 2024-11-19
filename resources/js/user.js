
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

