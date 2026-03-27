<div class="container d-flex justify-content-center align-items-center" style="height:100vh;">

    <div class="card p-4 shadow" style="width:420px; border-radius:15px;">

        <h3 class="text-center mb-3">Create Account</h3>

        <?php if($GLOBALS['msg']): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($GLOBALS['msg']) ?></div>
        <?php endif; ?>

        <form method="POST">

            <!-- USERNAME -->
            <div class="mb-3">
                <label>Username</label>
                <input 
                    type="text" 
                    name="username" 
                    class="form-control"
                    required>
            </div>

            <!-- EMAIL -->
            <div class="mb-3">
                <label>Email</label>
                <input 
                    type="email" 
                    name="email" 
                    class="form-control"
                    required>
            </div>

            <!-- PASSWORD -->
            <div class="mb-3">
                <label>Password</label>
                <input 
                    type="password" 
                    name="password" 
                    class="form-control"
                    required>
            </div>

            <!-- CONFIRM PASSWORD -->
            <div class="mb-3">
                <label>Confirm Password</label>
                <input 
                    type="password" 
                    name="confirm_password" 
                    class="form-control"
                    required>
            </div>

            <!-- BUTTON -->
            <button class="btn btn-primary w-100">Create Account</button>

        </form>

        <div class="text-center mt-3">
            <a href="index.php?mnu=login">Already have an account? Login</a>
        </div>

    </div>

</div>
