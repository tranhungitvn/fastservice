<?php
require_once 'config/config.php';
require_once 'includes/Database.php';
require_once 'includes/User.php';

session_start();

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

$errors = [];

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = new User();
    
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // Validate inputs
    if (empty($email)) {
        $errors[] = "Email is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }

    if (empty($password)) {
        $errors[] = "Password is required";
    }

    // Attempt login if no validation errors
    if (empty($errors)) {
        if ($user->login($email, $password)) {
            // Set remember me cookie if checked
            if (isset($_POST['remember_me'])) {
                $token = bin2hex(random_bytes(32));
                setcookie('remember_token', $token, time() + 30 * 24 * 60 * 60, '/');
                $user->storeRememberToken($_SESSION['user_id'], $token);
            }

            // Redirect based on user role
            switch($_SESSION['user_role']) {
                case 'admin':
                    header('Location: admin/dashboard.php');
                    break;
                case 'seller':
                    header('Location: seller/dashboard.php');
                    break;
                default:
                    // Redirect to intended page if set, otherwise go to index
                    $redirect = isset($_GET['redirect']) ? $_GET['redirect'] : 'index.php';
                    header("Location: $redirect");
            }
            exit();
        } else {
            $errors[] = "Invalid email or password";
        }
    }
}

require_once 'includes/header.php';
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow-sm">
                <div class="card-body p-5">
                    <h1 class="text-center mb-4">Login</h1>
            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                            <ul class="mb-0">
                        <?php foreach ($errors as $error): ?>
                            <li><?= htmlspecialchars($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

                    <form method="POST" action="login.php<?= isset($_GET['redirect']) ? '?redirect=' . htmlspecialchars($_GET['redirect']) : '' ?>" id="loginForm">
                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" 
                                   class="form-control" 
                                   id="email" 
                                   name="email" 
                           value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>"
                           required>
                </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <div class="input-group">
                                <input type="password" 
                                       class="form-control" 
                                       id="password" 
                                       name="password" 
                                       required>
                                <button class="btn btn-outline-secondary" 
                                        type="button" 
                                        id="togglePassword">
                                    <i class="fas fa-eye"></i>
                    </button>
                            </div>
                        </div>

                        <div class="mb-3 form-check">
                            <input type="checkbox" 
                                   class="form-check-input" 
                                   id="remember_me" 
                                   name="remember_me">
                            <label class="form-check-label" for="remember_me">
                                Remember me
                            </label>
            </div>

                        <button type="submit" class="btn btn-primary w-100 mb-3">
                            Login
                        </button>
                        <div class="text-center mb-3">
                            <a href="forgot-password.php" class="text-decoration-none">
                                Forgot your password?
                            </a>
            </div>

                        <div class="separator text-muted text-center mb-3">
                            <span>OR</span>
        </div>

                        <div class="social-login">
                            <button type="button" 
                                    class="btn btn-outline-danger w-100 mb-2"
                                    onclick="socialLogin('google')">
                                <i class="fab fa-google me-2"></i> Continue with Google
                            </button>
                            <button type="button" 
                                    class="btn btn-outline-primary w-100"
                                    onclick="socialLogin('facebook')">
                                <i class="fab fa-facebook-f me-2"></i> Continue with Facebook
                            </button>
    </div>
                    </form>
                </div>
                <div class="card-footer text-center py-3 bg-light">
                    Don't have an account? 
                    <a href="register.php" class="text-decoration-none">Sign up</a>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.separator {
    display: flex;
    align-items: center;
    text-align: center;
    margin: 1rem 0;
}

.separator::before,
.separator::after {
    content: '';
    flex: 1;
    border-bottom: 1px solid #dee2e6;
}

.separator span {
    padding: 0 1rem;
}

.social-login .btn {
    position: relative;
    text-align: center;
    padding: 0.5rem 1rem;
}

.social-login .btn i {
    position: absolute;
    left: 1rem;
    top: 50%;
    transform: translateY(-50%);
}
</style>
    <script>
document.addEventListener('DOMContentLoaded', function() {
    // Toggle password visibility
    const togglePassword = document.getElementById('togglePassword');
    const password = document.getElementById('password');
    
    togglePassword.addEventListener('click', function() {
        const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
        password.setAttribute('type', type);
        
        // Toggle eye icon
        const icon = this.querySelector('i');
        icon.classList.toggle('fa-eye');
        icon.classList.toggle('fa-eye-slash');
    });

    // Form validation
    const loginForm = document.getElementById('loginForm');
    loginForm.addEventListener('submit', function(e) {
        const email = document.getElementById('email').value;
        const password = document.getElementById('password').value;
        let isValid = true;

        // Simple email validation
        if (!email || !/\S+@\S+\.\S+/.test(email)) {
            isValid = false;
            showError('Please enter a valid email address');
        }
            // Password validation
            if (!password) {
            isValid = false;
            showError('Please enter your password');
            }

        if (!isValid) {
                e.preventDefault();
            }
        });
});

// Social login handler
        function socialLogin(provider) {
    // Here you would typically redirect to your OAuth handler
    const providers = {
        'google': 'auth/google-login.php',
        'facebook': 'auth/facebook-login.php'
    };
    
    if (providers[provider]) {
        window.location.href = providers[provider];
    }
}

// Error message helper
function showError(message) {
    // You could enhance this to show errors in a nicer way
    alert(message);
}
    </script>

<?php require_once 'includes/footer.php'; ?>
