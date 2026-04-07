  // Sidebar active state
  const links = document.querySelectorAll('.nav-link');
  links.forEach(link => {
    link.addEventListener('click', function(e) {
      e.preventDefault();
      links.forEach(l => l.classList.remove('active'));
      this.classList.add('active');
    });
  });

  // Role switch logic
  document.addEventListener('DOMContentLoaded', () => {
    const checkbox = document.getElementById('roleSwitch');
    const roleText = document.getElementById('roleText');
    const sliderIcon = document.getElementById('sliderIcon');
    const pinContainerLeft = document.getElementById('pinContainerLeft');
    const pinContainerRight = document.getElementById('pinContainerRight');
    const pinInputsLeft = pinContainerLeft.querySelectorAll('.pin-input');
    const pinInputsRight = pinContainerRight.querySelectorAll('.pin-input');
    const navMenu = document.querySelector('.nav-menu');

    const librarianLinks = [
      { href: '#', text: 'Requests', icon: '📄' },
      { href: '#', text: 'Non-Returned', icon: '⏰' },
      { href: '#', text: 'Issued Books', icon: '📚' },
      { href: '#', text: 'Logout', icon: '🚪', class: 'logout' }
    ];

    const adminLinks = [
      { href: '#', text: 'Dashboard', icon: '📊' },
      { href: '#', text: 'Create', icon: '➕' },
      { href: '#', text: 'Books', icon: '📘' },
      { href: '#', text: 'Logout', icon: '🚪', class: 'logout' }
    ];

    checkbox.addEventListener('change', () => {
      if (checkbox.checked) {
        roleText.textContent = 'Admin';
        roleText.classList.add('left');
        roleText.classList.remove('right');
        sliderIcon.textContent = '😻';
        sliderIcon.classList.add('move-right');
        updateNavLinks(adminLinks);
      } else {
        roleText.textContent = 'Librarian';
        roleText.classList.add('right');
        roleText.classList.remove('left');
        sliderIcon.textContent = '😺';
        sliderIcon.classList.remove('move-right');
        updateNavLinks(librarianLinks);
      }

      pinContainerLeft.classList.add('hidden');
      pinContainerRight.classList.add('hidden');
      roleText.style.display = '';
    });

    document.getElementById('switchLabel').addEventListener('click', (e) => {
      e.preventDefault();
      if (checkbox.checked) {
        togglePinContainer(pinContainerRight, pinInputsRight);
      } else {
        togglePinContainer(pinContainerLeft, pinInputsLeft);
      }
    });

    function togglePinContainer(container, inputs) {
      if (container.classList.contains('hidden')) {
        container.classList.remove('hidden');
        inputs[0].focus();
        roleText.style.display = 'none';
      }
    }

    function checkPin(container, inputs, correctPin) {
      const enteredPin = Array.from(inputs).map(input => input.value).join('');
      if (enteredPin.length === 4) {
        if (enteredPin === correctPin) {
          checkbox.checked = !checkbox.checked;
          checkbox.dispatchEvent(new Event('change'));
        } else {
          alert('Incorrect PIN. Try again.');
        }

        inputs.forEach(input => input.value = '');
        container.classList.add('hidden');
        roleText.style.display = '';
      }
    }

    pinInputsRight.forEach((input, index) => {
      setupPinInput(input, index, pinInputsRight, pinContainerRight, '5678');
    });

    pinInputsLeft.forEach((input, index) => {
      setupPinInput(input, index, pinInputsLeft, pinContainerLeft, '1234');
    });

    function setupPinInput(input, index, inputs, container, correctPin) {
      input.addEventListener('input', () => {
        input.value = input.value.replace(/\D/, '');
        if (input.value.length === 1 && index < inputs.length - 1) {
          inputs[index + 1].focus();
        }
        checkPin(container, inputs, correctPin);
      });

      input.addEventListener('keydown', (e) => {
        if (e.key === 'Backspace' && input.value === '' && index > 0) {
          inputs[index - 1].focus();
        }
      });
    }

    function updateNavLinks(linksData) {
      const navMenu = document.querySelector('.nav-menu');
      navMenu.innerHTML = '';
      linksData.forEach((linkData, index) => {
        const link = document.createElement('a');
        link.href = linkData.href;
        link.className = `nav-link ${linkData.class || ''} ${index === 0 ? 'active' : ''}`;
        link.innerHTML = `<span class="icon">${linkData.icon}</span>${linkData.text}`;
        navMenu.appendChild(link);
      });

      const links = navMenu.querySelectorAll('.nav-link');
      links.forEach(link => {
        link.addEventListener('click', function(e) {
          e.preventDefault();
          links.forEach(l => l.classList.remove('active'));
          this.classList.add('active');
        });
      });
    }
  });
