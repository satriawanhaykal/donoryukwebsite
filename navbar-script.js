// navbar-script.js
document.addEventListener('DOMContentLoaded', () => {
    const userInfoNavbar = document.getElementById('user-info-navbar');
    const adminDashboardNavLink = document.getElementById('admin-dashboard-nav-link');
    const logoutNavbarDisplay = document.getElementById('logout-navbar-display');
    const logoutNavbarBtn = document.getElementById('logout-navbar-btn');

    // Dropdown elements
    const donorDropdownBtn = document.getElementById('donor-dropdown-btn');
    const donorDropdownContent = document.getElementById('donor-dropdown-content');

    const BASE_API_URL = 'https://donoryuk.xyz/donoryuk_backend/api/';


    // Function to update navbar based on user login status
    function updateNavbarState() {
        const user = JSON.parse(localStorage.getItem("user")); // Get user info here

        if (user) {
            if (userInfoNavbar) {
                userInfoNavbar.style.display = 'list-item';
                const userFullname = user.fullname;
                let displayUsername = '';

                if (userFullname) {
                    const nameParts = userFullname.split(' ');
                    if (nameParts.length > 0) {
                        displayUsername = nameParts[0];
                    } else {
                        displayUsername = userFullname.charAt(0);
                    }
                }
                userInfoNavbar.querySelector('.nav-user-info').textContent = `Halo, ${displayUsername}`;
            }

            if (adminDashboardNavLink) {
                if (user.role === 'admin') {
                    adminDashboardNavLink.style.display = 'list-item';
                } else {
                    adminDashboardNavLink.style.display = 'none';
                }
            }

            if (logoutNavbarDisplay) {
                logoutNavbarDisplay.style.display = 'list-item';
            }
            // Hide login/register links if user is logged in
            const loginNavLink = document.getElementById('login-navbar-link');
            const registerNavLink = document.getElementById('register-navbar-link');
            if (loginNavLink) loginNavLink.style.display = 'none';
            if (registerNavLink) registerNavLink.style.display = 'none';

        } else {
            // If user is not logged in, hide user-specific elements
            if (userInfoNavbar) userInfoNavbar.style.display = 'none';
            if (adminDashboardNavLink) adminDashboardNavLink.style.display = 'none';
            if (logoutNavbarDisplay) logoutNavbarDisplay.style.display = 'none';

            // Show login/register links if they exist
            const loginNavLink = document.getElementById('login-navbar-link');
            const registerNavLink = document.getElementById('register-navbar-link');
            if (loginNavLink) loginNavLink.style.display = 'list-item';
            if (registerNavLink) registerNavLink.style.display = 'list-item';
        }
    }

    // Call updateNavbarState when the DOM is fully loaded
    updateNavbarState(); // Initial call

    // Event listener for logout button
    if (logoutNavbarBtn) {
        logoutNavbarBtn.addEventListener('click', async (e) => {
            e.preventDefault();
            try {
                const response = await fetch(`${BASE_API_URL}logout.php`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    }
                });
                const data = await response.json();
                if (data.success) {
                    localStorage.removeItem("user");
                    updateNavbarState(); // Update navbar immediately after logout
                    window.location.href = "index.html"; // Redirect to home
                } else {
                    alert('Logout gagal: ' + (data.message || 'Terjadi kesalahan tidak diketahui.'));
                }
            } catch (error) {
                console.error('Error saat logout:', error);
                alert('Terjadi kesalahan saat menghubungi server. Pastikan server PHP Anda berjalan.');
            }
        });
    }

    // JavaScript for dropdown toggle (assuming donor dropdown)
    if (donorDropdownBtn && donorDropdownContent) {
        donorDropdownBtn.addEventListener('click', (e) => {
            e.preventDefault();
            donorDropdownContent.classList.toggle('show');
        });

        window.addEventListener('click', (e) => {
            if (!e.target.matches('#donor-dropdown-btn') && !e.target.closest('.dropdown-content')) {
                if (donorDropdownContent.classList.contains('show')) {
                    donorDropdownContent.classList.remove('show');
                }
            }
        });
    }

    // This script should NOT contain calls to loadHospitals() or loadPendonorData()
    // Those functions should be called specifically from admin_dashboard.html's own script block.
});