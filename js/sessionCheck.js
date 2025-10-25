async function updateNavbar(page) {
  const loginLink = document.getElementById('login-link');
  const registerLink = document.getElementById('register-link');
  const logoutLink = document.getElementById('logout-link');

  try {
    const res = await fetch('api/check_session.php');
    const d = await res.json();

    if (d.loggedIn) {
      // ✅ Logged-in state
      loginLink.textContent = `Welcome, ${d.username}`;
      loginLink.href = page;
      loginLink.classList.remove('btn-light');
      loginLink.classList.add('btn-success');

      registerLink.style.display = 'none';
      logoutLink.classList.remove('d-none');
    } else {
      // ❌ Logged-out state
      loginLink.textContent = 'Login';
      loginLink.href = 'login.html';
      loginLink.classList.remove('btn-success');
      loginLink.classList.add('btn-light');

      registerLink.style.display = 'inline-block';
      logoutLink.classList.add('d-none');
    }
  } catch (err) {
    console.error('Error checking session:', err);
  }
}

// 🧩 Handle logout with instant UI refresh
document.getElementById('logout-link').addEventListener('click', async e => {
  e.preventDefault();
  const res = await fetch('api/logout.php');
  const data = await res.json();
  if (data.success) {
    // Instantly refresh navbar to logged-out state
    updateNavbar();
  }
});
