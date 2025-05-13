$(document).ready(function() {
    // Load books when page loads
    loadBooks();

    // Search form submission
    $('#searchForm').submit(function(e) {
        e.preventDefault();
        const title = $('#searchTitle').val();
        const author = $('#searchAuthor').val();
        const genre = $('#searchGenre').val();
        
        loadBooks(title, author, genre);
    });

    // Clear search
    $('#clearSearch').click(function() {
        $('#searchForm')[0].reset();
        loadBooks();
    });

    // Book form submission (for both add and edit)
    $('#bookForm').submit(function(e) {
        e.preventDefault();
        
        const formData = new FormData();
        formData.append('title', $('#title').val());
        formData.append('author', $('#author').val());
        formData.append('genre', $('#genre').val());
        formData.append('description', $('#description').val());
        formData.append('published_date', $('#publishedDate').val());
        
        const bookId = $('#bookId').val();
        const fileInput = $('#coverImage')[0];
        
        if (fileInput.files.length > 0) {
            formData.append('cover_image', fileInput.files[0]);
        }

        if (bookId) {
            // Update existing book
            formData.append('id', bookId);
            updateBook(formData);
        } else {
            // Add new book
            addBook(formData);
        }
    });

    // Preview cover image before upload
    $('#coverImage').change(function() {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                $('#coverPreview').html(`<img src="${e.target.result}" class="img-thumbnail">`);
                $('#uploadArea').addClass('has-image');
            }
            reader.readAsDataURL(file);
        }
    });

    // Drag and drop for cover image
    const uploadArea = document.getElementById('uploadArea');
    if (uploadArea) {
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            uploadArea.addEventListener(eventName, preventDefaults, false);
        });

        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }

        ['dragenter', 'dragover'].forEach(eventName => {
            uploadArea.addEventListener(eventName, highlight, false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            uploadArea.addEventListener(eventName, unhighlight, false);
        });

        function highlight() {
            uploadArea.classList.add('highlight');
        }

        function unhighlight() {
            uploadArea.classList.remove('highlight');
        }

        uploadArea.addEventListener('drop', handleDrop, false);

        function handleDrop(e) {
            const dt = e.dataTransfer;
            const files = dt.files;
            $('#coverImage')[0].files = files;
            $('#coverImage').trigger('change');
        }
    }
});

// Load books from API
function loadBooks(title = '', author = '', genre = '') {
    let url = 'read.php';
    
    if (title || author || genre) {
        url = 'search.php';
        const params = new URLSearchParams();
        if (title) params.append('title', title);
        if (author) params.append('author', author);
        if (genre) params.append('genre', genre);
        url += `?${params.toString()}`;
    }

    $.ajax({
        url: url,
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            console.log('Books loaded:', response);
            const books = response.data || [];
            renderBooksTable(books);
            $('#bookCount').text(books.length);
        },
        error: function(xhr, status, error) {
            console.error('Error loading books:', error);
            showAlert('Error loading books. Please try again.', 'danger');
        }
    });
}

// Render books in the table
function renderBooksTable(books) {
    const tbody = $('#booksTableBody');
    tbody.empty();

    if (books.length === 0) {
        tbody.append('<tr><td colspan="6" class="text-center py-4">No books found in your collection</td></tr>');
        return;
    }

    books.forEach((book) => {
        // Fix for cover image path - prepend uploads/ if it's just a filename
        let coverImagePath = book.cover_image;
        if (coverImagePath && !coverImagePath.startsWith('http') && !coverImagePath.startsWith('uploads/')) {
            coverImagePath = 'uploads/' + coverImagePath;
        }

        const coverImage = book.cover_image 
            ? `<img src="${coverImagePath}" class="book-cover" onerror="this.onerror=null; this.src='default_image.jpg';">`
            : '<div class="no-cover"><i class="fas fa-book"></i></div>';

        const row = `
            <tr>
                <td data-label="Cover">${coverImage}</td>
                <td data-label="Title">${book.title || 'Untitled'}</td>
                <td data-label="Author">${book.author || 'Unknown'}</td>
                <td data-label="Genre">${book.genre || 'Uncategorized'}</td>
                <td data-label="Published">${book.published_date || 'Unknown'}</td>
                <td data-label="Actions" class="actions">
                    <button class="btn btn-sm btn-primary edit-btn" data-id="${book.id}">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn btn-sm btn-danger delete-btn" data-id="${book.id}">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>
        `;
        tbody.append(row);
    });

    // Add event listeners to action buttons
    $('.edit-btn').click(function() {
        const bookId = $(this).data('id');
        editBook(bookId);
    });

    $('.delete-btn').click(function() {
        const bookId = $(this).data('id');
        showConfirmDialog(
            'Delete Book', 
            'Are you sure you want to delete this book? This action cannot be undone.',
            'Delete',
            'Cancel',
            () => deleteBook(bookId)
        );
    });
}

// Add new book
function addBook(formData) {
    $.ajax({
        url: 'create.php',
        type: 'POST',
        data: formData,
        contentType: false,
        processData: false,
        success: function(response) {
            console.log('Book added:', response);
            $('#bookModal').modal('hide');
            loadBooks();
            showAlert('Book added successfully!', 'success');
        },
        error: function(xhr, status, error) {
            console.error('Error adding book:', error);
            showAlert('Error adding book. Please try again.', 'danger');
        }
    });
}

// Edit book - load data into modal
function editBook(bookId) {
    $.ajax({
        url: `read.php?id=${bookId}`,
        type: 'GET',
        dataType: 'json',
        success: function(book) {
            $('#bookModalLabel').text('Edit Book');
            $('#bookId').val(book.id);
            $('#title').val(book.title);
            $('#author').val(book.author);
            $('#genre').val(book.genre);
            $('#description').val(book.description);
            $('#publishedDate').val(book.published_date);

            // Show current cover image if exists
            if (book.cover_image) {
                let coverImagePath = book.cover_image;
                if (!coverImagePath.startsWith('http') && !coverImagePath.startsWith('uploads/')) {
                    coverImagePath = 'uploads/' + coverImagePath;
                }
                $('#coverPreview').html(`<img src="${coverImagePath}" class="img-thumbnail">`);
                $('#uploadArea').addClass('has-image');
            } else {
                $('#coverPreview').empty();
                $('#uploadArea').removeClass('has-image');
            }

            $('#bookModal').modal('show');
        },
        error: function(xhr, status, error) {
            console.error('Error loading book:', error);
            showAlert('Error loading book for editing. Please try again.', 'danger');
        }
    });
}

// Update book
function updateBook(formData) {
    $.ajax({
        url: 'update.php',  // Changed from create.php to update.php
        type: 'POST',
        data: formData,
        contentType: false,
        processData: false,
        success: function(response) {
            console.log('Book updated:', response);
            $('#bookModal').modal('hide');
            loadBooks();
            showAlert('Book updated successfully!', 'success');
        },
        error: function(xhr, status, error) {
            console.error('Error updating book:', error);
            showAlert('Error updating book. Please try again.', 'danger');
        }
    });
}

// Delete book
function deleteBook(bookId) {
    $.ajax({
        url: 'delete.php',
        type: 'DELETE',
        data: JSON.stringify({ id: bookId }),
        contentType: 'application/json',
        success: function(response) {
            loadBooks();
            showAlert('Book deleted successfully!', 'success');
        },
        error: function(xhr, status, error) {
            console.error('Error deleting book:', error);
            showAlert('Error deleting book. Please try again.', 'danger');
        }
    });
}

// Reset form when modal is closed
$('#bookModal').on('hidden.bs.modal', function() {
    $('#bookForm')[0].reset();
    $('#coverPreview').empty();
    $('#bookId').val('');
    $('#bookModalLabel').text('Add New Book');
    $('#uploadArea').removeClass('has-image');
});

// Show alert message
function showAlert(message, type) {
    const alert = $(`
        <div class="alert alert-${type} alert-dismissible fade show" role="alert" 
             style="position: fixed; top: 20px; right: 20px; z-index: 9999; min-width: 300px;">
            ${message}
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    `);
    
    $('body').append(alert);
    
    setTimeout(() => {
        alert.alert('close');
    }, 5000);
}

// Show confirmation dialog
function showConfirmDialog(title, message, confirmText, cancelText, confirmCallback) {
    const modal = $(`
        <div class="modal fade" id="confirmDialog" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title">${title}</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p>${message}</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">${cancelText}</button>
                        <button type="button" class="btn btn-danger" id="confirmButton">${confirmText}</button>
                    </div>
                </div>
            </div>
        </div>
    `);
    
    $('body').append(modal);
    
    const confirmModal = new bootstrap.Modal(document.getElementById('confirmDialog'));
    confirmModal.show();
    
    $('#confirmButton').click(function() {
        confirmCallback();
        confirmModal.hide();
    });
    
    $('#confirmDialog').on('hidden.bs.modal', function() {
        modal.remove();
    });
}