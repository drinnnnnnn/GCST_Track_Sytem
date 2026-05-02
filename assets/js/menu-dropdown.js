(function () {
  const MENU_HTML_URL = 'http://localhost/GCST_Track_System/includes/menu-dropdown.html';

  function select(selector) {
    return document.querySelector(selector);
  }

  function insertDropdownMarkup() {
    const placeholder = document.getElementById('dropdown-placeholder');
    if (!placeholder || document.getElementById('dropdown-menu')) {
      return Promise.resolve();
    }

    return fetch(MENU_HTML_URL, { cache: 'no-store' })
      .then((response) => {
        if (!response.ok) {
          throw new Error('Failed to load dropdown markup.');
        }
        return response.text();
      })
      .then((html) => {
        placeholder.innerHTML = html;
      })
      .catch((error) => {
        console.error(error);
      });
  }

  function positionDropdown(dropdown, trigger) {
    const rect = trigger.getBoundingClientRect();
    const menuWidth = Math.min(dropdown.offsetWidth || 260, window.innerWidth - 32);
    const right = Math.min(window.innerWidth - 16, rect.right + 0);
    const left = Math.max(16, right - menuWidth);
    const top = rect.bottom + 10;
    const spaceBelow = window.innerHeight - rect.bottom - 16;
    const spaceAbove = rect.top - 16;
    let finalTop = top;

    if (dropdown.offsetHeight > spaceBelow && spaceAbove > dropdown.offsetHeight) {
      finalTop = rect.top - dropdown.offsetHeight - 10;
    }

    dropdown.style.left = `${left}px`;
    dropdown.style.top = `${finalTop}px`;
    dropdown.style.width = `${menuWidth}px`;
  }

  function closeDropdown(dropdown, trigger) {
    if (!dropdown) return;
    dropdown.classList.remove('open');
    if (trigger) {
      trigger.setAttribute('aria-expanded', 'false');
    }
  }

  function openDropdown(dropdown, trigger) {
    if (!dropdown) return;
    positionDropdown(dropdown, trigger);
    dropdown.classList.add('open');
    if (trigger) {
      trigger.setAttribute('aria-expanded', 'true');
    }
  }

  function toggleDropdown(event, dropdown, trigger) {
    event.preventDefault();
    event.stopPropagation();
    if (!dropdown) return;
    dropdown.classList.contains('open') ? closeDropdown(dropdown, trigger) : openDropdown(dropdown, trigger);
  }

  function setupDropdown() {
    const trigger = select('#menu-icon');
    if (!trigger) {
      return;
    }

    const triggerClassList = trigger.classList;
    if (!triggerClassList.contains('menu-icon')) {
      triggerClassList.add('menu-icon');
    }
    if (!triggerClassList.contains('menu-trigger')) {
      triggerClassList.add('menu-trigger');
    }
    trigger.setAttribute('aria-haspopup', 'true');
    trigger.setAttribute('aria-expanded', 'false');
    if (trigger.tagName.toLowerCase() === 'button') {
      trigger.type = 'button';
    }

    const dropdown = select('#dropdown-menu');
    if (!dropdown) {
      return;
    }

    trigger.addEventListener('click', (event) => toggleDropdown(event, dropdown, trigger));

    trigger.addEventListener('keydown', (event) => {
      if (event.key === 'Enter' || event.key === ' ') {
        toggleDropdown(event, dropdown, trigger);
      }
    });

    document.addEventListener('click', (event) => {
      if (!dropdown.contains(event.target) && !trigger.contains(event.target)) {
        closeDropdown(dropdown, trigger);
      }
    });

    document.addEventListener('keydown', (event) => {
      if (event.key === 'Escape') {
        closeDropdown(dropdown, trigger);
      }
    });

    dropdown.addEventListener('click', (event) => {
      if (event.target.closest('.menu-item')) {
        closeDropdown(dropdown, trigger);
      }
    });

    window.addEventListener('resize', () => closeDropdown(dropdown, trigger));
    window.addEventListener('scroll', () => closeDropdown(dropdown, trigger), { passive: true });
  }

  function initialize() {
    insertDropdownMarkup().then(() => {
      if (window.lucide && typeof lucide.createIcons === 'function') {
        lucide.createIcons();
      }
      setupDropdown();

      if (!window.lucide || typeof lucide.createIcons !== 'function') {
        window.addEventListener('load', () => {
          if (window.lucide && typeof lucide.createIcons === 'function') {
            lucide.createIcons();
          }
        });
      }
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initialize);
  } else {
    initialize();
  }
})();

