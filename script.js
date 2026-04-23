// script.js
$(document).ready(function() {
    // Global variables
    let currentUser = null;
    let visitorCount = 1245;

    // Initialize the application
    initApp();

    // Initialize application
    function initApp() {
        // Check if user is logged in
        checkAuthStatus();
        
        // Initialize event listeners
        initEventListeners();
        
        // Initialize skill bars animation
        initSkillBars();
        
        // Update visitor count
        updateVisitorCount();
        
        // Load user projects if logged in
        if (currentUser) {
            loadUserProjects();
        }
    }

    // Check authentication status
    function checkAuthStatus() {
        const savedUser = localStorage.getItem('currentUser');
        if (savedUser) {
            currentUser = JSON.parse(savedUser);
            showUserSection();
            updateUserDisplay();
        } else {
            showGuestSection();
        }
    }

    // Initialize all event listeners
    function initEventListeners() {
        // Navigation
        $('a[data-page]').on('click', handleNavigation);
        $('.hamburger').on('click', toggleMobileMenu);

        // Forms
        $('#contactForm').on('submit', handleContactForm);
        $('#loginForm').on('submit', handleLogin);
        $('#registerForm').on('submit', handleRegister);
        $('#logout-btn').on('click', handleLogout);

        // Dashboard
        $('.tab-btn').on('click', handleTabSwitch);
        $('#profile-form').on('submit', handleProfileUpdate);
        $('#password-form').on('submit', handlePasswordChange);
        $('#add-project-btn').on('click', showAddProjectModal);

        // Form validation
        $('.form-control').on('blur', validateField);
        $('.form-control').on('input', clearError);
    }

    // Navigation handling
    function handleNavigation(e) {
        e.preventDefault();
        const page = $(this).data('page');
        showPage(page);
        
        // Update active nav link
        $('.nav-links a').removeClass('active');
        $(this).addClass('active');
        
        // Close mobile menu if open
        if ($('.nav-links').hasClass('active')) {
            toggleMobileMenu();
        }
    }

    // Show specific page
    function showPage(pageName) {
        $('.page').removeClass('active');
        $(`#${pageName}`).addClass('active');
        
        // Special handling for specific pages
        if (pageName === 'skills') {
            setTimeout(animateSkillBars, 300);
        } else if (pageName === 'dashboard' && currentUser) {
            loadDashboardData();
        }
    }

    // Toggle mobile menu
    function toggleMobileMenu() {
        $('.nav-links').toggleClass('active');
        $('.hamburger i').toggleClass('fa-bars fa-times');
    }

    // Authentication functions
    function handleLogin(e) {
        e.preventDefault();
        
        if (validateForm('loginForm')) {
            const email = $('#login-email').val();
            const password = $('#login-password').val();
            
            // Simulate API call - in real app, this would be a server request
            simulateAPICall(() => {
                const users = JSON.parse(localStorage.getItem('users') || '[]');
                const user = users.find(u => u.email === email && u.password === password);
                
                if (user) {
                    currentUser = {
                        id: user.id,
                        name: user.name,
                        username: user.username,
                        email: user.email,
                        joinDate: user.joinDate
                    };
                    
                    localStorage.setItem('currentUser', JSON.stringify(currentUser));
                    showUserSection();
                    updateUserDisplay();
                    showPage('dashboard');
                    showToast('Login successful!', 'success');
                } else {
                    showToast('Invalid email or password', 'error');
                }
            });
        }
    }

    function handleRegister(e) {
        e.preventDefault();
        
        if (validateForm('registerForm')) {
            const name = $('#register-name').val();
            const email = $('#register-email').val();
            const username = $('#register-username').val();
            const password = $('#register-password').val();
            
            // Check if user already exists
            const users = JSON.parse(localStorage.getItem('users') || '[]');
            if (users.find(u => u.email === email)) {
                showToast('User with this email already exists', 'error');
                return;
            }
            
            if (users.find(u => u.username === username)) {
                showToast('Username already taken', 'error');
                return;
            }
            
            // Create new user
            const newUser = {
                id: Date.now(),
                name: name,
                username: username,
                email: email,
                password: password,
                joinDate: new Date().toLocaleDateString(),
                bio: '',
                website: '',
                location: '',
                projects: []
            };
            
            users.push(newUser);
            localStorage.setItem('users', JSON.stringify(users));
            
            showToast('Registration successful! Please login.', 'success');
            showPage('login');
            $('#registerForm')[0].reset();
        }
    }

    function handleLogout(e) {
        e.preventDefault();
        currentUser = null;
        localStorage.removeItem('currentUser');
        showGuestSection();
        showPage('home');
        showToast('Logged out successfully', 'success');
    }

    // Show/hide auth sections
    function showUserSection() {
        $('#guest-links').hide();
        $('#user-links').show();
    }

    function showGuestSection() {
        $('#guest-links').show();
        $('#user-links').hide();
    }

    // Update user display across pages
    function updateUserDisplay() {
        if (currentUser) {
            $('#username-display').text(currentUser.username);
            $('#home-username').text(`Hello! I'm ${currentUser.name}`);
            $('#about-username').text(`Hello! I'm ${currentUser.name}`);
            $('#dashboard-username').text(currentUser.name);
            $('#dashboard-email').text(currentUser.email);
            $('#join-date').text(currentUser.joinDate);
            
            // Update profile form
            $('#profile-name').val(currentUser.name);
            $('#profile-username').val(currentUser.username);
            $('#profile-bio').val(currentUser.bio || '');
            $('#profile-website').val(currentUser.website || '');
            $('#profile-location').val(currentUser.location || '');
        }
    }

    // Contact form handling
    function handleContactForm(e) {
        e.preventDefault();
        
        if (validateForm('contactForm')) {
            simulateAPICall(() => {
                showToast('Message sent successfully!', 'success');
                $('#contactForm')[0].reset();
            });
        }
    }

    // Dashboard functions
    function handleTabSwitch() {
        const tab = $(this).data('tab');
        
        $('.tab-btn').removeClass('active');
        $(this).addClass('active');
        
        $('.tab-pane').removeClass('active');
        $(`#tab-${tab}`).addClass('active');
    }

    function handleProfileUpdate(e) {
        e.preventDefault();
        
        if (currentUser) {
            currentUser.name = $('#profile-name').val();
            currentUser.username = $('#profile-username').val();
            currentUser.bio = $('#profile-bio').val();
            currentUser.website = $('#profile-website').val();
            currentUser.location = $('#profile-location').val();
            
            localStorage.setItem('currentUser', JSON.stringify(currentUser));
            
            // Update users array
            const users = JSON.parse(localStorage.getItem('users') || '[]');
            const userIndex = users.findIndex(u => u.id === currentUser.id);
            if (userIndex !== -1) {
                users[userIndex] = { ...users[userIndex], ...currentUser };
                localStorage.setItem('users', JSON.stringify(users));
            }
            
            updateUserDisplay();
            showToast('Profile updated successfully!', 'success');
        }
    }

    function handlePasswordChange(e) {
        e.preventDefault();
        
        const currentPassword = $('#current-password').val();
        const newPassword = $('#new-password').val();
        const confirmPassword = $('#confirm-new-password').val();
        
        if (!currentPassword) {
            showToast('Please enter current password', 'error');
            return;
        }
        
        if (newPassword.length < 8) {
            showToast('New password must be at least 8 characters', 'error');
            return;
        }
        
        if (newPassword !== confirmPassword) {
            showToast('Passwords do not match', 'error');
            return;
        }
        
        // Verify current password
        const users = JSON.parse(localStorage.getItem('users') || '[]');
        const user = users.find(u => u.id === currentUser.id);
        
        if (user && user.password === currentPassword) {
            user.password = newPassword;
            localStorage.setItem('users', JSON.stringify(users));
            showToast('Password updated successfully!', 'success');
            $('#password-form')[0].reset();
        } else {
            showToast('Current password is incorrect', 'error');
        }
    }

    function loadDashboardData() {
        if (currentUser) {
            const users = JSON.parse(localStorage.getItem('users') || '[]');
            const user = users.find(u => u.id === currentUser.id);
            
            if (user) {
                $('#projects-count').text(user.projects ? user.projects.length : 0);
                $('#skills-count').text(6); // Hardcoded for demo
                $('#visits-count').text(Math.floor(Math.random() * 100) + 50); // Random visits
            }
        }
    }

    function loadUserProjects() {
        if (currentUser) {
            const users = JSON.parse(localStorage.getItem('users') || '[]');
            const user = users.find(u => u.id === currentUser.id);
            const projectsList = $('#user-projects-list');
            
            projectsList.empty();
            
            if (user && user.projects && user.projects.length > 0) {
                user.projects.forEach(project => {
                    projectsList.append(`
                        <div class="project-card">
                            <div class="project-image">
                                <img src="${project.image}" alt="${project.title}">
                            </div>
                            <div class="project-info">
                                <h3>${project.title}</h3>
                                <p>${project.description}</p>
                                <button class="btn btn-danger btn-sm" onclick="deleteProject(${project.id})">Delete</button>
                            </div>
                        </div>
                    `);
                });
            } else {
                projectsList.html('<p class="no-projects">No projects yet. Click "Add Project" to get started!</p>');
            }
        }
    }

    function showAddProjectModal() {
        const projectTitle = prompt('Enter project title:');
        if (!projectTitle) return;
        
        const projectDescription = prompt('Enter project description:');
        if (!projectDescription) return;
        
        const newProject = {
            id: Date.now(),
            title: projectTitle,
            description: projectDescription,
            image: 'https://images.unsplash.com/photo-1551650975-87deedd944c3?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80'
        };
        
        // Add project to user
        const users = JSON.parse(localStorage.getItem('users') || '[]');
        const userIndex = users.findIndex(u => u.id === currentUser.id);
        
        if (userIndex !== -1) {
            if (!users[userIndex].projects) {
                users[userIndex].projects = [];
            }
            users[userIndex].projects.push(newProject);
            localStorage.setItem('users', JSON.stringify(users));
            
            loadUserProjects();
            loadDashboardData();
            showToast('Project added successfully!', 'success');
        }
    }

    // Skill bars animation
    function initSkillBars() {
        $('.skill-progress').each(function() {
            const width = $(this).data('width');
            $(this).css('width', '0%');
        });
    }

    function animateSkillBars() {
        $('.skill-progress').each(function() {
            const width = $(this).data('width');
            $(this).css('width', width).addClass('animate');
        });
    }

    // Form validation
    function validateForm(formId) {
        let isValid = true;
        const form = document.getElementById(formId);
        
        // Reset all errors
        $(`#${formId} .error`).hide();
        
        // Validate each field based on form type
        if (formId === 'contactForm') {
            if (!$('#name').val()) {
                $('#nameError').show();
                isValid = false;
            }
            if (!$('#email').val() || !isValidEmail($('#email').val())) {
                $('#emailError').show();
                isValid = false;
            }
            if (!$('#subject').val()) {
                $('#subjectError').show();
                isValid = false;
            }
            if (!$('#message').val()) {
                $('#messageError').show();
                isValid = false;
            }
        } else if (formId === 'loginForm') {
            if (!$('#login-email').val() || !isValidEmail($('#login-email').val())) {
                $('#loginEmailError').show();
                isValid = false;
            }
            if (!$('#login-password').val()) {
                $('#loginPasswordError').show();
                isValid = false;
            }
        } else if (formId === 'registerForm') {
            if (!$('#register-name').val()) {
                $('#registerNameError').show();
                isValid = false;
            }
            if (!$('#register-email').val() || !isValidEmail($('#register-email').val())) {
                $('#registerEmailError').show();
                isValid = false;
            }
            if (!$('#register-username').val()) {
                $('#registerUsernameError').show();
                isValid = false;
            }
            if (!$('#register-password').val() || $('#register-password').val().length < 8) {
                $('#registerPasswordError').show();
                isValid = false;
            }
            if ($('#register-password').val() !== $('#register-confirm').val()) {
                $('#registerConfirmError').show();
                isValid = false;
            }
            if (!$('#terms').is(':checked')) {
                showToast('Please accept the Terms of Service', 'error');
                isValid = false;
            }
        }
        
        return isValid;
    }

    function validateField() {
        const field = $(this);
        const fieldId = field.attr('id');
        const value = field.val();
        
        // Clear previous error
        clearError.call(this);
        
        // Validate based on field type
        if (fieldId === 'email' || fieldId === 'login-email' || fieldId === 'register-email') {
            if (value && !isValidEmail(value)) {
                showFieldError(fieldId, 'Please enter a valid email');
            }
        } else if (fieldId === 'register-password') {
            if (value && value.length < 8) {
                showFieldError(fieldId, 'Password must be at least 8 characters');
            }
        } else if (fieldId === 'register-confirm') {
            const password = $('#register-password').val();
            if (value && value !== password) {
                showFieldError(fieldId, 'Passwords do not match');
            }
        } else if (field.attr('required') && !value) {
            showFieldError(fieldId, 'This field is required');
        }
    }

    function showFieldError(fieldId, message) {
        $(`#${fieldId}Error`).text(message).show();
    }

    function clearError() {
        const fieldId = $(this).attr('id');
        $(`#${fieldId}Error`).hide();
    }

    function isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }

    // Toast notification
    function showToast(message, type = 'success') {
        const toast = $('#toast');
        toast.text(message);
        toast.removeClass('success error').addClass(type);
        toast.addClass('show');
        
        setTimeout(() => {
            toast.removeClass('show');
        }, 3000);
    }

    // Visitor counter
    function updateVisitorCount() {
        // Simulate increasing visitor count
        const savedCount = localStorage.getItem('visitorCount');
        if (savedCount) {
            visitorCount = parseInt(savedCount);
        }
        
        visitorCount += Math.floor(Math.random() * 5) + 1;
        localStorage.setItem('visitorCount', visitorCount.toString());
        
        $('#visitor-count').text(visitorCount.toLocaleString());
    }

    // Change avatar (placeholder function)
    function changeAvatar() {
        showToast('Avatar change functionality would be implemented here', 'success');
    }

    // Avatar change functionality
function handleAvatarUpload(event) {
    const file = event.target.files[0];
    if (file) {
        // Check file size (max 2MB)
        if (file.size > 2 * 1024 * 1024) {
            showToast('File size too large. Please choose an image under 2MB.', 'error');
            return;
        }

        // Check file type
        if (!file.type.match('image.*')) {
            showToast('Please select a valid image file.', 'error');
            return;
        }

        const reader = new FileReader();
        reader.onload = function(e) {
            // Update avatar preview
            $('#user-avatar').attr('src', e.target.result);
            $('#settings-avatar').attr('src', e.target.result);
            
            // Save to user data
            if (currentUser) {
                currentUser.avatar = e.target.result;
                localStorage.setItem('currentUser', JSON.stringify(currentUser));
                
                // Update users array
                const users = JSON.parse(localStorage.getItem('users') || '[]');
                const userIndex = users.findIndex(u => u.id === currentUser.id);
                if (userIndex !== -1) {
                    users[userIndex].avatar = e.target.result;
                    localStorage.setItem('users', JSON.stringify(users));
                }
                
                showToast('Avatar updated successfully!', 'success');
            }
        };
        reader.readAsDataURL(file);
    }
}

// Alternative avatar upload from settings tab
function setupAvatarUpload() {
    $('#avatar-upload').on('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            // Check file size (max 2MB)
            if (file.size > 2 * 1024 * 1024) {
                showToast('File size too large. Please choose an image under 2MB.', 'error');
                return;
            }

            // Check file type
            if (!file.type.match('image.*')) {
                showToast('Please select a valid image file.', 'error');
                return;
            }

            const reader = new FileReader();
            reader.onload = function(e) {
                // Show preview
                $('#preview-image').attr('src', e.target.result);
                $('#avatar-preview').show();
            };
            reader.readAsDataURL(file);
        }
    });
}

function saveAvatar() {
    const previewSrc = $('#preview-image').attr('src');
    if (previewSrc && currentUser) {
        // Update all avatar images
        $('#user-avatar').attr('src', previewSrc);
        $('#settings-avatar').attr('src', previewSrc);
        
        // Save to user data
        currentUser.avatar = previewSrc;
        localStorage.setItem('currentUser', JSON.stringify(currentUser));
        
        // Update users array
        const users = JSON.parse(localStorage.getItem('users') || '[]');
        const userIndex = users.findIndex(u => u.id === currentUser.id);
        if (userIndex !== -1) {
            users[userIndex].avatar = previewSrc;
            localStorage.setItem('users', JSON.stringify(users));
        }
        
        $('#avatar-preview').hide();
        $('#avatar-upload').val('');
        showToast('Avatar updated successfully!', 'success');
    }
}

function cancelAvatarUpload() {
    $('#avatar-preview').hide();
    $('#avatar-upload').val('');
}

// Update the initApp function to load saved avatars
function initApp() {
    // Check if user is logged in
    checkAuthStatus();
    
    // Initialize event listeners
    initEventListeners();
    
    // Initialize skill bars animation
    initSkillBars();
    
    // Update visitor count
    updateVisitorCount();
    
    // Setup avatar upload
    setupAvatarUpload();
    
    // Load user projects if logged in
    if (currentUser) {
        loadUserProjects();
    }
}

// Update checkAuthStatus to load avatar
function checkAuthStatus() {
    const savedUser = localStorage.getItem('currentUser');
    if (savedUser) {
        currentUser = JSON.parse(savedUser);
        showUserSection();
        updateUserDisplay();
        
        // Load saved avatar if exists
        if (currentUser.avatar) {
            $('#user-avatar').attr('src', currentUser.avatar);
            $('#settings-avatar').attr('src', currentUser.avatar);
        }
    } else {
        showGuestSection();
    }
}

// Make functions available globally
window.handleAvatarUpload = handleAvatarUpload;
window.saveAvatar = saveAvatar;
window.cancelAvatarUpload = cancelAvatarUpload;

    // Delete project
    function deleteProject(projectId) {
        if (confirm('Are you sure you want to delete this project?')) {
            const users = JSON.parse(localStorage.getItem('users') || '[]');
            const userIndex = users.findIndex(u => u.id === currentUser.id);
            
            if (userIndex !== -1 && users[userIndex].projects) {
                users[userIndex].projects = users[userIndex].projects.filter(p => p.id !== projectId);
                localStorage.setItem('users', JSON.stringify(users));
                
                loadUserProjects();
                loadDashboardData();
                showToast('Project deleted successfully!', 'success');
            }
        }
    }

    // Simulate API call with delay
    function simulateAPICall(callback) {
        setTimeout(callback, 1000);
    }

    // Make functions available globally for onclick handlers
    window.changeAvatar = changeAvatar;
    window.deleteProject = deleteProject;
});

// User Management System
class UserManager {
    constructor() {
        this.currentUser = null;
        this.users = JSON.parse(localStorage.getItem('portfolioUsers')) || [];
        this.init();
    }

    init() {
        // Check for logged-in user on page load
        const savedUser = localStorage.getItem('currentUser');
        if (savedUser) {
            this.currentUser = JSON.parse(savedUser);
            this.updateUIForLoggedInUser();
        }
    }

    // Register new user
    register(userData) {
        // Check if user already exists
        if (this.users.find(user => user.email === userData.email)) {
            throw new Error('User already exists with this email');
        }
        if (this.users.find(user => user.username === userData.username)) {
            throw new Error('Username already taken');
        }

        const newUser = {
            id: Date.now().toString(),
            ...userData,
            joinDate: new Date().toISOString(),
            avatar: 'https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80',
            bio: 'I\'m a web development student with a passion for creating beautiful, functional websites and applications.',
            hobbies: 'When I\'m not coding, you can find me exploring new technologies, contributing to open source projects, or hiking in the great outdoors.',
            location: '',
            website: '',
            projects: [],
            stats: {
                projectsCount: 0,
                skillsCount: 6,
                visitsCount: 0
            }
        };

        this.users.push(newUser);
        localStorage.setItem('portfolioUsers', JSON.stringify(this.users));
        
        return this.login(userData.email, userData.password);
    }

    // Login user
    login(email, password) {
        const user = this.users.find(u => u.email === email && u.password === password);
        if (!user) {
            throw new Error('Invalid email or password');
        }

        this.currentUser = user;
        localStorage.setItem('currentUser', JSON.stringify(user));
        this.updateUIForLoggedInUser();
        
        return user;
    }

    // Logout user
    logout() {
        this.currentUser = null;
        localStorage.removeItem('currentUser');
        this.updateUIForLoggedOutUser();
    }

    // Update user profile
    updateProfile(updatedData) {
        const userIndex = this.users.findIndex(u => u.id === this.currentUser.id);
        if (userIndex !== -1) {
            this.users[userIndex] = { ...this.users[userIndex], ...updatedData };
            this.currentUser = this.users[userIndex];
            localStorage.setItem('portfolioUsers', JSON.stringify(this.users));
            localStorage.setItem('currentUser', JSON.stringify(this.currentUser));
            this.updateUIForLoggedInUser();
        }
    }

    // Update UI when user is logged in
    updateUIForLoggedInUser() {
        // Update navigation
        document.getElementById('guest-links').style.display = 'none';
        document.getElementById('user-links').style.display = 'flex';
        document.getElementById('username-display').textContent = this.currentUser.username;

        // Update home page content
        this.updateHomePageContent();
        
        // Update about page
        this.updateAboutPageContent();
        
        // Update dashboard
        this.updateDashboard();

        showToast(`Welcome back, ${this.currentUser.username}!`, 'success');
    }

    // Update UI when user logs out
    updateUIForLoggedOutUser() {
        // Update navigation
        document.getElementById('guest-links').style.display = 'flex';
        document.getElementById('user-links').style.display = 'none';

        // Reset home page to default content
        this.resetHomePageContent();
        
        // Reset about page to default content
        this.resetAboutPageContent();

        showToast('Logged out successfully!', 'success');
    }

    // Update home page with user data
    updateHomePageContent() {
        const usernameElement = document.querySelector('.dynamic-username');
        const bioElement = document.querySelector('.dynamic-bio');
        const hobbiesElement = document.querySelector('.dynamic-hobbies');
        const photoElement = document.getElementById('dynamic-user-photo');

        if (usernameElement) {
            usernameElement.textContent = this.currentUser.username;
        }
        if (bioElement && this.currentUser.bio) {
            bioElement.textContent = this.currentUser.bio;
        }
        if (hobbiesElement && this.currentUser.hobbies) {
            hobbiesElement.textContent = this.currentUser.hobbies;
        }
        if (photoElement && this.currentUser.avatar) {
            photoElement.src = this.currentUser.avatar;
        }
    }

    // Reset home page to default content
    resetHomePageContent() {
        const usernameElement = document.querySelector('.dynamic-username');
        const bioElement = document.querySelector('.dynamic-bio');
        const hobbiesElement = document.querySelector('.dynamic-hobbies');
        const photoElement = document.getElementById('dynamic-user-photo');

        if (usernameElement) {
            usernameElement.textContent = 'Rahul Patel';
        }
        if (bioElement) {
            bioElement.textContent = 'I\'m a web development student with a passion for creating beautiful, functional websites and applications. I enjoy turning complex problems into simple, intuitive solutions.';
        }
        if (hobbiesElement) {
            hobbiesElement.textContent = 'When I\'m not coding, you can find me exploring new technologies, contributing to open source projects, or hiking in the great outdoors.';
        }
        if (photoElement) {
            photoElement.src = 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80';
        }
    }

    // Update about page with user data
    updateAboutPageContent() {
        const aboutUsername = document.getElementById('about-username');
        if (aboutUsername) {
            aboutUsername.textContent = `Hello! I'm ${this.currentUser.username}`;
        }
    }

    // Reset about page to default content
    resetAboutPageContent() {
        const aboutUsername = document.getElementById('about-username');
        if (aboutUsername) {
            aboutUsername.textContent = "Hello! I'm Rahul Patel";
        }
    }

    // Update dashboard with user data
    updateDashboard() {
        const dashboardUsername = document.getElementById('dashboard-username');
        const dashboardEmail = document.getElementById('dashboard-email');
        const joinDate = document.getElementById('join-date');
        const projectsCount = document.getElementById('projects-count');
        const skillsCount = document.getElementById('skills-count');
        const visitsCount = document.getElementById('visits-count');

        if (dashboardUsername) dashboardUsername.textContent = this.currentUser.username;
        if (dashboardEmail) dashboardEmail.textContent = this.currentUser.email;
        if (joinDate) joinDate.textContent = new Date(this.currentUser.joinDate).getFullYear();
        if (projectsCount) projectsCount.textContent = this.currentUser.stats.projectsCount;
        if (skillsCount) skillsCount.textContent = this.currentUser.stats.skillsCount;
        if (visitsCount) visitsCount.textContent = this.currentUser.stats.visitsCount;

        // Update profile form
        this.populateProfileForm();
    }

    // Populate profile form with user data
    populateProfileForm() {
        document.getElementById('profile-name').value = this.currentUser.name || '';
        document.getElementById('profile-username').value = this.currentUser.username || '';
        document.getElementById('profile-bio').value = this.currentUser.bio || '';
        document.getElementById('profile-website').value = this.currentUser.website || '';
        document.getElementById('profile-location').value = this.currentUser.location || '';
    }
}

// Initialize User Manager
const userManager = new UserManager();

// Event Listeners for Authentication
document.addEventListener('DOMContentLoaded', function() {
    // Login Form
    const loginForm = document.getElementById('loginForm');
    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const email = document.getElementById('login-email').value;
            const password = document.getElementById('login-password').value;

            try {
                userManager.login(email, password);
                navigateToPage('home');
            } catch (error) {
                showToast(error.message, 'error');
            }
        });
    }

    // Register Form
    const registerForm = document.getElementById('registerForm');
    if (registerForm) {
        registerForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const userData = {
                name: document.getElementById('register-name').value,
                email: document.getElementById('register-email').value,
                username: document.getElementById('register-username').value,
                password: document.getElementById('register-password').value
            };

            try {
                userManager.register(userData);
                navigateToPage('home');
            } catch (error) {
                showToast(error.message, 'error');
            }
        });
    }

    // Logout Button
    const logoutBtn = document.getElementById('logout-btn');
    if (logoutBtn) {
        logoutBtn.addEventListener('click', function(e) {
            e.preventDefault();
            userManager.logout();
            navigateToPage('home');
        });
    }

    // Profile Form
    const profileForm = document.getElementById('profile-form');
    if (profileForm) {
        profileForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const updatedData = {
                name: document.getElementById('profile-name').value,
                username: document.getElementById('profile-username').value,
                bio: document.getElementById('profile-bio').value,
                website: document.getElementById('profile-website').value,
                location: document.getElementById('profile-location').value
            };

            userManager.updateProfile(updatedData);
            showToast('Profile updated successfully!', 'success');
        });
    }
});

// Toast Notification Function
function showToast(message, type = 'info') {
    const toast = document.getElementById('toast');
    toast.textContent = message;
    toast.className = 'toast show ' + type;
    
    setTimeout(() => {
        toast.classList.remove('show');
    }, 3000);
}

// Page Navigation Function (existing from your script)
function navigateToPage(pageId) {
    // Your existing page navigation logic
    document.querySelectorAll('.page').forEach(page => {
        page.classList.remove('active');
    });
    document.getElementById(pageId).classList.add('active');
    
    document.querySelectorAll('.nav-links a').forEach(link => {
        link.classList.remove('active');
    });
    document.querySelector(`[data-page="${pageId}"]`).classList.add('active');
}