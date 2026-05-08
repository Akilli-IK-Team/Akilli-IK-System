<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IKSystem - Register</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <div class="glass-container">
        <h1>Create New Account</h1>

        <div class="tab-container">
            <div class="tab active" onclick="switchTab('candidate')">Candidate Registration</div>
            <div class="tab" onclick="switchTab('employer')">Employer Registration</div>
        </div>

        <form action="islem.php" method="POST" id="registerForm" accept-charset="UTF-8">
            <input type="hidden" name="action" value="register">
            <input type="hidden" name="user_type" id="userType" value="candidate">

            <!-- Candidate Fields -->
            <div id="candidateFields">
                <div class="form-group">
                    <label for="first_name">First Name</label>
                    <input type="text" id="first_name" name="first_name" placeholder="Your first name">
                </div>
                <div class="form-group">
                    <label for="last_name">Last Name</label>
                    <input type="text" id="last_name" name="last_name" placeholder="Your last name">
                </div>
                <div class="form-group">
                    <label for="birth_date">Date of Birth</label>
                    <input type="date" id="birth_date" name="birth_date">
                </div>
                <div class="form-group">
                    <label for="profession">Profession</label>
                    <input type="text" id="profession" name="profession" placeholder="e.g. Software Engineer">
                </div>
            </div>

            <!-- Employer Fields -->
            <div id="employerFields" style="display: none;">
                <div class="form-group">
                    <label for="company_name">Company Name</label>
                    <input type="text" id="company_name" name="company_name" placeholder="Name of your company">
                </div>
                <div class="form-group">
                    <label for="industry">Industry</label>
                    <input type="text" id="industry" name="industry" placeholder="e.g. Technology, Health">
                </div>
            </div>

            <!-- Common Fields -->
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" required placeholder="example@email.com">
            </div>
            <div class="form-group">
                <label for="phone">Phone Number</label>
                <input type="text" id="phone" name="phone" placeholder="05XXXXXXXXX" pattern="[0-9]{11}" minlength="11" maxlength="11" title="Please enter your 11-digit phone number (digits only)" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required placeholder="••••••••">
            </div>

            <button type="submit" class="btn">Register</button>
        </form>

        <div class="link-text">
            Already have an account? <a href="index.php">Login Here</a>
        </div>
    </div>

    <script>
        function switchTab(type) {
            document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
            if (type === 'candidate') {
                document.querySelectorAll('.tab')[0].classList.add('active');
                document.getElementById('candidateFields').style.display = 'block';
                document.getElementById('employerFields').style.display = 'none';
                
                // toggle required properties appropriately
                document.getElementById('first_name').required = true;
                document.getElementById('last_name').required = true;
                document.getElementById('company_name').required = false;

            } else {
                document.querySelectorAll('.tab')[1].classList.add('active');
                document.getElementById('candidateFields').style.display = 'none';
                document.getElementById('employerFields').style.display = 'block';

                document.getElementById('first_name').required = false;
                document.getElementById('last_name').required = false;
                document.getElementById('company_name').required = true;
            }
            document.getElementById('userType').value = type;
        }

        // Initialize required fields
        switchTab('candidate');
    </script>
</body>
</html>
