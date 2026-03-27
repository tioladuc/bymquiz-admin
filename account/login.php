<div class="container d-flex justify-content-center align-items-center" style="height:100vh;">

    <div class="card p-4 shadow" style="width:400px; border-radius:15px;">
        
        <h3 class="text-center mb-3">Login</h3>

        <?php if($GLOBALS['msg']): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($GLOBALS['msg']) ?></div>
        <?php endif; ?>

        <form method="POST">

            <!-- LOGIN -->
            <div class="mb-3">
                <label>Email or Username</label>
                <input 
                    type="text" 
                    name="email" 
                    class="form-control" 
                    required
                    placeholder="Enter email or username">
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

            <!-- ROLE -->
            <div class="mb-3">
                <label>Login As</label>
                <select name="typeuser" class="form-control">
                    <option value="contributor">Contributor</option>
                    <option value="admin">Admin</option>
                </select>
            </div>

            <!-- BUTTON -->
            <button class="btn btn-primary w-100">Login</button>

        </form>

        <div class="text-center mt-3">
            <a href="index.php?mnu=createaccount">Create Account</a> | 
            <a href="index.php?mnu=forgotpassword">Forgot Password?</a>
        </div>

    </div>

</div>