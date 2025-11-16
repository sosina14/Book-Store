## 📚 Bookstore Web Project

### 🔹 Description

This is a **simple Bookstore web application** built using **HTML, CSS, PHP**, and optionally **JavaScript**. It allows users to **log in**, and **manage books** (add, edit, delete, and search). The system is designed to be beginner-friendly while still covering essential web development concepts like forms, file uploads, dynamic content, and visual styling.

---

### 🔹 Features

#### 1. **Login Page (`home.html`)**

* Users enter their **Name**, **Email**, **Password**, and select a **Role** (Admin, Author, or Customer).
* After successful submission, users are redirected to the main Bookstore interface (`index.html`).
* Basic form validation is done using JavaScript.

#### 2. **Book Management (in `index.html` via PHP)**

* **Add a Book**:

  * Input: Title, Author, Upload Book Cover image.
  * Book data is stored, and the image is saved on the server.
* **Edit a Book**:

  * Users can update the book title, author, or replace the image.
* **Delete a Book**:

  * Remove a book and its cover image.
* **Search Books**:

  * Real-time search filtering by title or author name.

#### 3. **Image Processing**

* Uploaded book cover images are:

  * **Resized**, **cropped**, or **watermarked** using PHP's GD library (if implemented).
  * Saved in an `uploads/` directory.

#### 4. **Logging**

* User actions (add/edit/delete books) are optionally recorded into a **log file** for admin tracking.

#### 5. **Styling**

* Uses **Bootstrap** and **custom CSS** for a clean, responsive, and modern UI.
* Icons via **Font Awesome**.

---

### 🔹 Technologies Used

* **HTML5** – structure and content
* **CSS3** – styling (with Bootstrap & custom rules)
* **JavaScript** – front-end validation and redirects
* **PHP** – back-end logic (for books, images, logs)
* **GD Library (PHP)** – image processing (if enabled)
* **XAMPP** – local server setup

---

### 🔹 Folder Structure Example

```
/bookstore/
├── index.html
├── home.html
├── style.css
├── add_book.php
├── delete_book.php
├── edit_book.php
├── upload/              # Book cover images
├── log.txt              # Action logs
├── includes/
│   └── functions.php    # Image handling functions
```

---

### 🔹 How to Run the Project

1. Install **XAMPP** and start Apache.
2. Place your project folder inside the `htdocs` directory.
3. Access the site via: `http://localhost/bookstore/home.html`
4. Fill in your login details to enter the bookstore.

---

