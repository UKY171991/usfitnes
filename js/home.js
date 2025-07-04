/**
 * PathLab Pro Home Page JavaScript
 * Handles animations, scroll effects, and interactive features
 */

// jQuery easing function
$.easing.easeInOutQuart = function (x, t, b, c, d) {
  if ((t/=d/2) < 1) return c/2*t*t*t*t + b;
  return -c/2 * ((t-=2)*t*t*t - 2) + b;
};

$(document).ready(function() {
  // Initialize all features
  initScrollAnimations();
  initCounterAnimations();
  initParticleAnimations();
  initNavbarEffects();
  initButtonEffects();
  initSmoothScrolling();
  
  // Force navbar visibility on page load
  forceNavbarVisibility();
});

/**
 * Initialize smooth scrolling for anchor links
 */
function initSmoothScrolling() {
  $('a[href*="#"]').on('click', function (e) {
    e.preventDefault();
    const target = $($(this).attr('href'));
    if (target.length) {
      $('html, body').animate({
        scrollTop: target.offset().top - 80
      }, 800, 'easeInOutQuart');
    }
  });
}

/**
 * Initialize navbar scroll effects and mobile collapse
 */
function initNavbarEffects() {
  // Enhanced navbar on scroll
  $(window).scroll(function() {
    const scrollTop = $(this).scrollTop();
    const navbar = $('.navbar-custom');
    
    if (scrollTop > 50) {
      navbar.addClass('scrolled');
    } else {
      navbar.removeClass('scrolled');
    }
    
    // Parallax effect for hero section
    const heroSection = $('.hero-section');
    const parallaxSpeed = 0.3;
    heroSection.css('transform', `translateY(${scrollTop * parallaxSpeed}px)`);
  });

  // Auto-collapse navbar on mobile after clicking a link
  $('.navbar-nav .nav-link').on('click', function() {
    if ($(window).width() < 992) {
      $('.navbar-collapse').collapse('hide');
    }
  });
}

/**
 * Initialize scroll animations using Intersection Observer
 */
function initScrollAnimations() {
  const observerOptions = {
    threshold: 0.1,
    rootMargin: '0px 0px -50px 0px'
  };

  const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.classList.add('animated');
      }
    });
  }, observerOptions);

  document.querySelectorAll('.fade-in-up').forEach(el => {
    observer.observe(el);
  });
}

/**
 * Initialize counter animations for stats section
 */
function initCounterAnimations() {
  const counterObserver = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting && !entry.target.classList.contains('counted')) {
        entry.target.classList.add('counted');
        animateCounter(entry.target);
      }
    });
  }, { threshold: 0.5 });

  document.querySelectorAll('.counter').forEach(counter => {
    counterObserver.observe(counter);
  });
}

/**
 * Animate counter numbers
 * @param {Element} element - The counter element
 */
function animateCounter(element) {
  const target = parseInt(element.dataset.target);
  const duration = 2000;
  const step = target / (duration / 16);
  let current = 0;

  const timer = setInterval(() => {
    current += step;
    if (current >= target) {
      current = target;
      clearInterval(timer);
    }
    element.textContent = Math.floor(current) + '+';
  }, 16);
}

/**
 * Initialize floating particle animations
 */
function initParticleAnimations() {
  const particles = document.querySelectorAll('.particle');
  particles.forEach((particle, index) => {
    // Random size and animation duration
    const size = Math.random() * 10 + 5;
    const duration = Math.random() * 3 + 3;
    const delay = Math.random() * 2;
    
    particle.style.width = size + 'px';
    particle.style.height = size + 'px';
    particle.style.animationDuration = duration + 's';
    particle.style.animationDelay = delay + 's';
    
    // Random horizontal position
    particle.style.left = Math.random() * 100 + '%';
  });
}

/**
 * Initialize button hover effects
 */
function initButtonEffects() {
  // Feature card hover effects
  $('.feature-card').hover(
    function() {
      $(this).find('.feature-icon').addClass('animate__animated animate__pulse');
    },
    function() {
      $(this).find('.feature-icon').removeClass('animate__animated animate__pulse');
    }
  );

  // Enhanced button interactions
  $('.btn-primary-custom, .btn-outline-custom').hover(
    function() {
      $(this).addClass('animate__animated animate__pulse');
    },
    function() {
      $(this).removeClass('animate__animated animate__pulse');
    }
  );
}

/**
 * Force navbar visibility to ensure all links are shown
 */
function forceNavbarVisibility() {
  // Wait for DOM to be fully ready
  setTimeout(function() {
    // Ensure navbar collapse is properly shown on desktop
    const navbar = document.querySelector('.navbar-custom');
    const navbarCollapse = document.querySelector('#navbarNav');
    const navbarNav = document.querySelector('.navbar-nav');
    const navLinks = document.querySelectorAll('.navbar-nav .nav-link');
    
    if (navbar) {
      navbar.style.display = 'block';
      navbar.style.visibility = 'visible';
    }
    
    if (navbarCollapse) {
      // Force show collapse on desktop
      if (window.innerWidth >= 992) {
        navbarCollapse.classList.add('show');
        navbarCollapse.style.display = 'flex';
        navbarCollapse.style.visibility = 'visible';
        navbarCollapse.style.opacity = '1';
      }
    }
    
    if (navbarNav) {
      navbarNav.style.display = 'flex';
      navbarNav.style.visibility = 'visible';
      navbarNav.style.opacity = '1';
    }
    
    navLinks.forEach(function(link) {
      link.style.display = 'flex';
      link.style.visibility = 'visible';
      link.style.opacity = '1';
      link.style.color = '#2c5aa0';
    });
    
    console.log('Navbar visibility forced - Width:', window.innerWidth);
  }, 100);
  
  // Also handle window resize
  window.addEventListener('resize', function() {
    const navbarCollapse = document.querySelector('#navbarNav');
    if (navbarCollapse && window.innerWidth >= 992) {
      navbarCollapse.classList.add('show');
      navbarCollapse.style.display = 'flex';
    }
  });
}

/**
 * Create dynamic mouse particles
 */
function createMouseParticle(x, y) {
  const particle = document.createElement('div');
  particle.style.position = 'fixed';
  particle.style.left = x + 'px';
  particle.style.top = y + 'px';
  particle.style.width = '4px';
  particle.style.height = '4px';
  particle.style.background = 'rgba(102, 126, 234, 0.6)';
  particle.style.borderRadius = '50%';
  particle.style.pointerEvents = 'none';
  particle.style.zIndex = '9999';
  particle.style.animation = 'particleFade 1s ease-out forwards';
  
  document.body.appendChild(particle);
  
  setTimeout(() => {
    particle.remove();
  }, 1000);
}

/**
 * Typing effect for text (optional feature)
 * @param {Element} element - The element to apply typing effect to
 * @param {string} text - The text to type
 * @param {number} speed - Typing speed in milliseconds
 */
function typeWriter(element, text, speed = 100) {
  let i = 0;
  element.innerHTML = '';
  
  function type() {
    if (i < text.length) {
      element.innerHTML += text.charAt(i);
      i++;
      setTimeout(type, speed);
    }
  }
  
  setTimeout(type, 1000); // Start after 1 second
}

// Add dynamic particles on mouse move
document.addEventListener('mousemove', (e) => {
  if (Math.random() > 0.98) { // Only create particles occasionally
    createMouseParticle(e.clientX, e.clientY);
  }
});

// Uncomment to enable typing effect for hero title
// const heroTitle = document.querySelector('.hero-title');
// if (heroTitle) typeWriter(heroTitle, 'PathLab Pro');
