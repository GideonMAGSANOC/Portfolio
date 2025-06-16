<?php
include('connection.php');

if (isset($_POST["register"])) {
    $fullname = mysqli_real_escape_string($conn, $_POST["fullname"] ?? '');
    $titles_json = json_encode($_POST['title'] ?? []);
    $facebook = mysqli_real_escape_string($conn, $_POST["facebook"] ?? '');
    $linkedin = mysqli_real_escape_string($conn, $_POST["linkedin"] ?? '');
    $instagram = mysqli_real_escape_string($conn, $_POST["instagram"] ?? '');
    $contact = mysqli_real_escape_string($conn, $_POST["contact"] ?? '');
    $location = mysqli_real_escape_string($conn, $_POST["location"] ?? '');
    $introduction = mysqli_real_escape_string($conn, $_POST["introduction"] ?? '');
    $profile = mysqli_real_escape_string($conn, $_POST["profile"] ?? '');
    $about = mysqli_real_escape_string($conn, $_POST["about"] ?? '');

    // Upload Profile Picture
    $profile_picture_path = '';
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES['profile_picture']['name'], PATHINFO_EXTENSION));
        $new_name = md5(time() . $_FILES['profile_picture']['name']) . '.' . $ext;
        $target = 'uploads/profile_pictures/' . $new_name;
        if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $target)) {
            $profile_picture_path = $target;
        }
    }

    // Upload CV File (PDF only)
    $cvfile_path = '';
    if (isset($_FILES['cvfile']) && $_FILES['cvfile']['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES['cvfile']['name'], PATHINFO_EXTENSION));
        if ($ext === 'pdf') {
            $new_name = md5(time() . $_FILES['cvfile']['name']) . '.pdf';
            $target = 'uploads/cv_files/' . $new_name;
            if (move_uploaded_file($_FILES['cvfile']['tmp_name'], $target)) {
                $cvfile_path = $target;
            }
        }
    }

    // Skills and Ratings
    $skills_json = json_encode($_POST['skill'] ?? []);
    $skill_ratings_json = json_encode($_POST['skill_rating'] ?? []);

    // Job Experience
    $job_titles_json = json_encode($_POST['job_title'] ?? []);
    $job_dates_json = json_encode($_POST['job_date'] ?? []);
    $companies_json = json_encode($_POST['company'] ?? []);
    $job_descriptions_json = json_encode($_POST['job_description'] ?? []);

    // Courses
    $course_titles_json = json_encode($_POST['course_title'] ?? []);
    $course_dates_json = json_encode($_POST['course_date'] ?? []);
    $institutions_json = json_encode($_POST['institution'] ?? []);
    $course_descriptions_json = json_encode($_POST['course_description'] ?? []);

    // Work Images (multiple uploads)
    $uploadedWorkImages = [];
    if (!empty($_FILES['work_image']['name'][0])) {
        $totalFiles = count($_FILES['work_image']['name']);
        $uploadDir = 'uploads/work_images/';
        for ($i = 0; $i < $totalFiles; $i++) {
            if ($_FILES['work_image']['error'][$i] === UPLOAD_ERR_OK) {
                $tmpPath = $_FILES['work_image']['tmp_name'][$i];
                $fileName = $_FILES['work_image']['name'][$i];
                $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                $newName = md5(time() . $fileName . $i) . '.' . $ext;
                $destPath = $uploadDir . $newName;
                if (move_uploaded_file($tmpPath, $destPath)) {
                    $uploadedWorkImages[] = $destPath;
                }
            }
        }
    }
    $work_images_json = json_encode($uploadedWorkImages);
    $work_titles_json = json_encode($_POST['work_title'] ?? []);
    $work_descriptions_json = json_encode($_POST['work_description'] ?? []);
    $work_skills_json = json_encode($_POST['work_skill'] ?? []);

    // Services
    $service_titles_json = json_encode($_POST['service_title'] ?? []);
    $service_descriptions_json = json_encode($_POST['service_description'] ?? []);

    // Build dynamic update query (handle file fields conditionally)
    $query = "UPDATE information SET
        Titles = ?, Facebook = ?, LinkedIn = ?, Instagram = ?, ContactNumber = ?, Location = ?, Introduction = ?, Profile = ?,
        Skills = ?, About = ?, JobTitle = ?, JobDate = ?, Company = ?, JobDescription = ?,
        Course = ?, CourseDate = ?, Institution = ?, InstitutionDescription = ?,
        WorkImage = ?, WorkTitle = ?, WorkDescription = ?, WorkSkill = ?,
        ServiceTitle = ?, ServiceDescription = ?";

    $types = "sssssssssssssssssssssss"; // 23 fields
    $params = [
        $titles_json, $facebook, $linkedin, $instagram, $contact, $location, $introduction, $profile,
        $skills_json, $about, $job_titles_json, $job_dates_json, $companies_json, $job_descriptions_json,
        $course_titles_json, $course_dates_json, $institutions_json, $course_descriptions_json,
        $work_images_json, $work_titles_json, $work_descriptions_json, $work_skills_json,
        $service_titles_json, $service_descriptions_json
    ];

    // Add optional profile picture
    if ($profile_picture_path !== '') {
        $query .= ", ProfilePicture = ?";
        $types .= "s";
        $params[] = $profile_picture_path;
    }

    // Add optional CV
    if ($cvfile_path !== '') {
        $query .= ", CVLink = ?";
        $types .= "s";
        $params[] = $cvfile_path;
    }

    $query .= " WHERE Name = ?";
    $types .= "s";
    $params[] = $fullname;

    $stmt = $conn->prepare($query);
    $stmt->bind_param($types, ...$params);

    if ($stmt->execute()) {
        echo "Record for <strong>$fullname</strong> successfully updated!";
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>




<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Fonts -->
    <link rel="dns-prefetch" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Raleway:300,400,600" rel="stylesheet" type="text/css">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">

    <style>
        @import url(https://fonts.googleapis.com/css?family=Raleway:300,400,600);

        body {
            margin: 0;
            font-size: .9rem;
            font-weight: 400;
            line-height: 1.6;
            color: #212529;
            text-align: left;
            background-color: #f5f8fa;
        }

        col-md-6 i {
            margin-left: -30px;
            cursor: pointer;
        }

        .navbar-laravel {
            box-shadow: 0 2px 4px rgba(0, 0, 0, .04);
        }

        .navbar-brand, .nav-link, .my-form, .login-form {
            font-family: Raleway, sans-serif;
        }

        .my-form {
            padding-top: 1.5rem;
            padding-bottom: 1.5rem;
        }

        .my-form .row {
            margin-left: 0;
            margin-right: 0;
        }

        .login-form {
            padding-top: 1.5rem;
            padding-bottom: 1.5rem;
        }

        .login-form .row {
            margin-left: 0;
            margin-right: 0;
        }

        input[type=submit] {
            background-color: #0d4f83;
            border: none;
            color: white;
            padding: 16px 32px;
            text-decoration: none;
            margin: 4px 2px;
            cursor: pointer;
        }

        .invalid {
            border-color: red;
        }

        .bi-eye {
            cursor: pointer;
        }

        .asterisk {
            font-size: 1.5em; /* Adjust the size as needed */
            color: red; /* Optional: Change color to make it more visible */
        }

        /* Media Queries for Responsive Design */
        @media (max-width: 768px) {
            /* For tablets and smaller devices */
            body {
                font-size: 0.85rem; /* Adjust font size */
            }

            .navbar-brand {
                font-size: 1.1rem; /* Slightly smaller brand name */
            }

            .my-form, .login-form {
                padding: 1rem; /* Adjust padding for smaller screens */
            }

            input[type=submit] {
                padding: 12px 24px; /* Adjust button size */
            }

            col-md-6 i {
                margin-left: -20px; /* Adjust icon margin */
            }

            /* Center the form for mobile devices */
            .login-form, .my-form {
                margin-left: auto;
                margin-right: auto;
                width: 90%; /* Allow more space on mobile */
            }

            .navbar-laravel {
                padding: 0.5rem 1rem; /* Adjust navbar padding */
            }
        }

        @media (max-width: 480px) {
            /* For mobile phones */
            body {
                font-size: 0.8rem; /* Further reduce font size */
            }

            .navbar-brand {
                font-size: 1rem; /* Ensure navbar brand text doesn't overflow */
            }

            .login-form, .my-form {
                padding: 0.5rem; /* Adjust form padding */
                width: 100%; /* Use full width on mobile */
            }

            input[type=submit] {
                width: 100%; /* Make the submit button full-width */
                padding: 14px 0; /* Adjust button size */
            }

            .invalid {
                border-color: red;
            }

            /* Ensure the icon fits properly */
            col-md-6 i {
                margin-left: -10px; /* Further adjust icon margin */
            }

            /* Center navbar brand */
            .navbar-brand {
                text-align: center;
                width: 100%;
            }

            .bi-eye {
                margin-left: 0; /* Adjust eye icon position for mobile */
            }
        }

        textarea.form-control {
          width: 100%;
          resize: none; /* Disable manual resizing */
          box-sizing: border-box;
          overflow: hidden; /* Hide scrollbar */
        }


    </style>

    <link rel="icon" href="Favicon.png">
</head>
<body>

<main class="login-form">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">Update Portfolio</div><br><br>

                    <div class="card-body">
                        <h4>Personal Information</h4><br>
                        <form action="insert_information.php" method="POST" name="register" enctype="multipart/form-data">

                            <div class="form-group row">
                                <label for="fullname" class="col-md-4 col-form-label text-md-right">Name</label>
                                <div class="col-md-6">
                                    <input type="text" id="fullname" class="form-control" name="fullname" required>
                                </div>
                            </div>

                            <h5>Titles</h5>

                            <div class="form-group row">
                                <div class="col-md-12">
                                    <table class="table table-bordered" id="title_table">
                                        <thead>
                                            <tr>
                                                <th>Title</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td><input type="text" class="form-control" name="title[]"></td>
                                                <td class="text-center">
                                                    <button type="button" class="btn btn-danger btn-sm remove-row">X</button>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                    <button type="button" class="btn btn-primary btn-sm" id="add_title_row">Add More</button>
                                </div>
                            </div>


                            <div class="form-group row">
                                <label class="col-md-12 col-form-label">
                                    <strong>Facebook</strong>
                                </label>
                                <div class="col-md-6">
                                    <input type="text" id="facebook" class="form-control" name="facebook">
                                </div>
                            </div>

                            <div class="form-group row">
                                <label class="col-md-12 col-form-label">
                                    <strong>LinkedIn</strong>
                                </label>
                                <div class="col-md-6">
                                    <input type="text" id="linkedin" class="form-control" name="linkedin">
                                </div>
                            </div>

                            <div class="form-group row">
                                <label class="col-md-12 col-form-label">
                                    <strong>Instagram</strong>
                                </label>
                                <div class="col-md-6">
                                    <input type="text" id="instagram" class="form-control" name="instagram">
                                </div>
                            </div>

                            <div class="form-group row">
                                <label class="col-md-12 col-form-label">
                                    <strong>Contact Number</strong>
                                </label>
                                <div class="col-md-6">
                                    <input type="text" id="contact" class="form-control" name="contact">
                                </div>
                            </div>

                            <div class="form-group row">
                                <label class="col-md-12 col-form-label">
                                    <strong>Location</strong>
                                </label>
                                <div class="col-md-6">
                                    <input type="text" id="location" class="form-control" name="location">
                                </div>
                            </div>


                            <div class="form-group row">
                                <label class="col-md-12 col-form-label">
                                    <strong>CV file</strong>
                                </label>
                                <div class="col-md-6">
                                    <input type="file" id="cvfile" class="form-control" name="cvfile" accept="application/pdf">
                                </div>
                            </div>


                            <div class="form-group row">
                                <label class="col-md-12 col-form-label">
                                    <strong>Introduction</strong>
                                </label>
                                <div class="col-md-6">
                                    <textarea id="introduction" class="form-control" name="introduction"></textarea>
                                </div>
                            </div>


                            <div class="form-group row">
                                <label class="col-md-12 col-form-label">
                                    <strong>Profile</strong>
                                </label>
                                <div class="col-md-6">
                                    <textarea id="profile" class="form-control" name="profile"></textarea>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label class="col-md-12 col-form-label">
                                    <strong>Profile Picture</strong>
                                </label>
                                <div class="col-md-6">
                                    <input type="file" id="profile_picture" class="form-control" name="profile_picture" accept=".jpg, .jpeg, image/jpeg">
                                </div>
                            </div>


                            <h5>Skills</h5>

                            <div class="form-group row" id="skills_container">
                                <div class="col-12 skill-entry border rounded p-3 mb-3">
                                    <div class="form-group">
                                        <label>Skill</label>
                                        <input type="text" class="form-control" name="skill[]">
                                    </div>
                                    <div class="form-group">
                                        <label>Skill Rating</label>
                                        <input type="number" class="form-control" name="skill_rating[]">
                                    </div>
                                    <div class="form-group text-right">
                                        <button type="button" class="btn btn-danger btn-sm remove-skill">Remove</button>
                                    </div>
                                </div>
                            </div>

                            <button type="button" class="btn btn-primary btn-sm" id="add_skill_row">Add More</button><br><br>

                            <h4>Work Experience</h4>

                            <div class="form-group row">
                                <label class="col-md-12 col-form-label">
                                    <strong>About</strong>
                                </label>
                                <div class="col-md-6">
                                    <input type="text" id="about" class="form-control" name="about">
                                </div>
                            </div>

                            <div class="form-group row" id="job_container">
                                <div class="col-12 job-entry border rounded p-3 mb-3">
                                    <div class="form-group">
                                        <label>Job Title</label>
                                        <input type="text" class="form-control" name="job_title[]">
                                    </div>
                                    <div class="form-group">
                                        <label>Date</label>
                                        <input type="text" class="form-control" name="job_date[]">
                                    </div>
                                    <div class="form-group">
                                        <label>Company</label>
                                        <input type="text" class="form-control" name="company[]">
                                    </div>
                                    <div class="form-group">
                                        <label>Description</label>
                                        <textarea class="form-control" name="job_description[]"></textarea>
                                    </div>
                                    <div class="form-group text-right">
                                        <button type="button" class="btn btn-danger btn-sm remove-job">Remove</button>
                                    </div>
                                </div>
                            </div>

                            <button type="button" class="btn btn-primary btn-sm" id="add_job">Add More</button><br><br>



                            <h4>Education</h4>

                            <div class="form-group row" id="course_container">
                                <div class="col-12 course-entry border rounded p-3 mb-3">
                                    <div class="form-group">
                                        <label>Course</label>
                                        <input type="text" class="form-control" name="course_title[]">
                                    </div>
                                    <div class="form-group">
                                        <label>Date</label>
                                        <input type="text" class="form-control" name="course_date[]">
                                    </div>
                                    <div class="form-group">
                                        <label>Institution</label>
                                        <input type="text" class="form-control" name="institution[]">
                                    </div>
                                    <div class="form-group">
                                        <label>Description</label>
                                        <textarea class="form-control" name="course_description[]"></textarea>
                                    </div>
                                    <div class="form-group text-right">
                                        <button type="button" class="btn btn-danger btn-sm remove-course">Remove</button>
                                    </div>
                                </div>
                            </div>

                            <button type="button" class="btn btn-primary btn-sm" id="add_course">Add More</button><br><br>


                            <h4>Works</h4>

                            <div class="form-group row" id="work_container">
                                <div class="col-12 work-entry border rounded p-3 mb-3">
                                    <div class="form-group">
                                        <label>Picture (URL or Path)</label>
                                        <input type="file" class="form-control" name="work_image[]" accept=".jpg,.jpeg,.png">
                                    </div>
                                    <div class="form-group">
                                        <label>Title</label>
                                        <input type="text" class="form-control" name="work_title[]">
                                    </div>
                                    <div class="form-group">
                                        <label>Description</label>
                                        <textarea class="form-control" name="work_description[]"></textarea>
                                    </div>
                                    <div class="form-group">
                                        <label>Skill</label>
                                        <input type="text" class="form-control" name="work_skill[]">
                                    </div>
                                    <div class="form-group text-right">
                                        <button type="button" class="btn btn-danger btn-sm remove-work">Remove</button>
                                    </div>
                                </div>
                            </div>

                            <button type="button" class="btn btn-primary btn-sm" id="add_work">Add More</button><br><br>


                            <h4>Services</h4>

                            <div class="form-group row" id="services_container">
                                <div class="col-12 service-entry border rounded p-3 mb-3">
                                    <div class="form-group">
                                        <label>Service Title</label>
                                        <input type="text" class="form-control" name="service_title[]">
                                    </div>
                                    <div class="form-group">
                                        <label>Service Description</label>
                                        <textarea class="form-control" name="service_description[]"></textarea>
                                    </div>
                                    <div class="form-group text-right">
                                        <button type="button" class="btn btn-danger btn-sm remove-services">Remove</button>
                                    </div>
                                </div>
                            </div>

                            <button type="button" class="btn btn-primary btn-sm" id="add_services">Add More</button><br><br>

                            <div class="col-md-6 offset-md-4">
                                <input type="submit" value="Update" name="register" class="btn btn-primary">
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>


                        <script>
                            document.querySelectorAll('textarea.form-control').forEach(function(textarea) {
                                textarea.addEventListener('input', function() {
                                  // Reset the height to shrink the textarea back when deleting text
                                  textarea.style.height = 'auto';
                                  // Set the height based on the scrollHeight, which adjusts automatically
                                  textarea.style.height = (textarea.scrollHeight) + 'px';
                                });
                              });

                            document.getElementById('add_services').addEventListener('click', function () {
                                const container = document.getElementById('services_container');
                                const newEntry = document.createElement('div');
                                newEntry.classList.add('col-12', 'service-entry', 'border', 'rounded', 'p-3', 'mb-3');
                                newEntry.innerHTML = `
                                    <div class="form-group">
                                        <label>Title</label>
                                        <input type="text" class="form-control" name="service_title[]">
                                    </div>
                                    <div class="form-group">
                                        <label>Description</label>
                                        <textarea class="form-control" name="service_description[]"></textarea>
                                    </div>
                                    <div class="form-group text-right">
                                        <button type="button" class="btn btn-danger btn-sm remove-services">Remove</button>
                                    </div>
                                `;
                                container.appendChild(newEntry);
                            });

                            document.addEventListener('click', function (e) {
                                if (e.target.classList.contains('remove-services')) {
                                    e.target.closest('.service-entry').remove();
                                }
                            });


                            document.getElementById('add_work').addEventListener('click', function () {
                                const container = document.getElementById('work_container');
                                const newEntry = document.createElement('div');
                                newEntry.classList.add('col-12', 'work-entry', 'border', 'rounded', 'p-3', 'mb-3');
                                newEntry.innerHTML = `
                                    <div class="form-group">
                                        <label>Picture (URL or Path)</label>
                                        <input type="file" class="form-control" name="work_image[]" accept=".jpg,.jpeg,.png">
                                    </div>
                                    <div class="form-group">
                                        <label>Title</label>
                                        <input type="text" class="form-control" name="work_title[]">
                                    </div>
                                    <div class="form-group">
                                        <label>Description</label>
                                        <textarea class="form-control" name="work_description[]"></textarea>
                                    </div>
                                    <div class="form-group">
                                        <label>Skill</label>
                                        <input type="text" class="form-control" name="work_skill[]">
                                    </div>
                                    <div class="form-group text-right">
                                        <button type="button" class="btn btn-danger btn-sm remove-work">Remove</button>
                                    </div>
                                `;
                                container.appendChild(newEntry);
                            });

                            document.addEventListener('click', function (e) {
                                if (e.target.classList.contains('remove-work')) {
                                    e.target.closest('.work-entry').remove();
                                }
                            });


                            document.getElementById('add_course').addEventListener('click', function () {
                                const container = document.getElementById('course_container');
                                const newEntry = document.createElement('div');
                                newEntry.classList.add('col-12', 'course-entry', 'border', 'rounded', 'p-3', 'mb-3');
                                newEntry.innerHTML = `
                                    <div class="form-group">
                                        <label>Course</label>
                                        <input type="text" class="form-control" name="course_title[]">
                                    </div>
                                    <div class="form-group">
                                        <label>Date</label>
                                        <input type="text" class="form-control" name="course_date[]">
                                    </div>
                                    <div class="form-group">
                                        <label>Institution</label>
                                        <input type="text" class="form-control" name="institution[]">
                                    </div>
                                    <div class="form-group">
                                        <label>Description</label>
                                        <textarea class="form-control" name="course_description[]"></textarea>
                                    </div>
                                    <div class="form-group text-right">
                                        <button type="button" class="btn btn-danger btn-sm remove-course">Remove</button>
                                    </div>
                                `;
                                container.appendChild(newEntry);
                            });

                            document.addEventListener('click', function (e) {
                                if (e.target.classList.contains('remove-course')) {
                                    e.target.closest('.course-entry').remove();
                                }
                            });


                            document.getElementById('add_job').addEventListener('click', function () {
                                const container = document.getElementById('job_container');
                                const newEntry = document.createElement('div');
                                newEntry.classList.add('col-12', 'job-entry', 'border', 'rounded', 'p-3', 'mb-3');
                                newEntry.innerHTML = `
                                    <div class="form-group">
                                        <label>Job Title</label>
                                        <input type="text" class="form-control" name="job_title[]">
                                    </div>
                                    <div class="form-group">
                                        <label>Date</label>
                                        <input type="text" class="form-control" name="job_date[]">
                                    </div>
                                    <div class="form-group">
                                        <label>Company</label>
                                        <input type="text" class="form-control" name="company[]">
                                    </div>
                                    <div class="form-group">
                                        <label>Description</label>
                                        <textarea class="form-control" name="job_description[]"></textarea>
                                    </div>
                                    <div class="form-group text-right">
                                        <button type="button" class="btn btn-danger btn-sm remove-job">Remove</button>
                                    </div>
                                `;
                                container.appendChild(newEntry);
                            });

                            document.addEventListener('click', function (e) {
                                if (e.target.classList.contains('remove-job')) {
                                    e.target.closest('.job-entry').remove();
                                }
                            });


                            document.getElementById('add_skill_row').addEventListener('click', function () {
                                const container = document.getElementById('skills_container');
                                const newEntry = document.createElement('div');
                                newEntry.classList.add('col-12', 'skill-entry', 'border', 'rounded', 'p-3', 'mb-3');
                                newEntry.innerHTML = `
                                    <div class="form-group">
                                        <label>Skill</label>
                                        <input type="text" class="form-control" name="skill[]">
                                    </div>
                                    <div class="form-group">
                                        <label>Skill Rating</label>
                                        <input type="number" class="form-control" name="skill_rating[]">
                                    </div>
                                    <div class="form-group text-right">
                                        <button type="button" class="btn btn-danger btn-sm remove-skill">Remove</button>
                                    </div>
                                `;
                                container.appendChild(newEntry);
                            });

                            document.addEventListener('click', function (e) {
                                if (e.target.classList.contains('remove-skill')) {
                                    e.target.closest('.skill-entry').remove();
                                }
                            });


                            document.getElementById('add_title_row').addEventListener('click', function () {
                                let table = document.getElementById('title_table').getElementsByTagName('tbody')[0];
                                let newRow = table.insertRow();
                                newRow.innerHTML = `
                                    <td><input type="text" class="form-control" name="skill[]"></td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-danger btn-sm remove-row">X</button>
                                    </td>
                                `;

                                newRow.querySelector('.remove-row').addEventListener('click', function () {
                                    this.closest('tr').remove();
                                });
                            });

                            document.querySelectorAll('.remove-row').forEach(button => {
                                button.addEventListener('click', function () {
                                    this.closest('tr').remove();
                                });
                            });

                            const toggle = document.getElementById('togglePassword');
                            const password = document.getElementById('password');
                            const confirmPassword = document.getElementById('confirm_password');
                            const passwordError = document.getElementById('passwordError');
                        
                            
                            toggle.addEventListener('click', function() {
                                if (password.type === "password") {
                                    password.type = 'text';
                                    this.classList.remove('bi-eye-slash');
                                    this.classList.add('bi-eye');
                                } else {
                                    password.type = 'password';
                                    this.classList.remove('bi-eye');
                                    this.classList.add('bi-eye-slash');
                                }
                            });
                            
                            

                            // Password validation function
                            function validatePassword() {
                                const passwordValue = password.value;
                                const confirmPasswordValue = confirmPassword.value;

                                const passwordRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/;
                                const isValidPassword = passwordRegex.test(passwordValue);

                                if (!isValidPassword) {
                                    password.classList.add('invalid');
                                    passwordError.textContent = "Password must be at least 8 characters long, include at least one uppercase letter, one lowercase letter, one number, and one special character.";
                                    return false;
                                } else if (passwordValue !== confirmPasswordValue) {
                                    password.classList.add('invalid');
                                    passwordError.textContent = "Passwords do not match.";
                                    return false;
                                } else {
                                    password.classList.remove('invalid');
                                    passwordError.textContent = "";
                                    return true;
                                }
                            }

                            // Validate password on input
                            password.addEventListener('input', validatePassword);
                            confirmPassword.addEventListener('input', validatePassword);

                            // Ensure password is validated before form submission
                            document.querySelector('form').addEventListener('submit', function(event) {
                                if (!validatePassword()) {
                                    event.preventDefault(); // Prevent form submission if password validation fails
                                }
                            });
                        </script>


<!-- Bootstrap JS and jQuery -->
<script src="//cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
<script src="//maxcdn.bootstrapcdn.com/bootstrap/4.1.1/js/bootstrap.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.3.0/font/bootstrap-icons.css"></script>

</body>
</html>