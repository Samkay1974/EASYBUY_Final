// countries.js - Country validation and autocomplete
document.addEventListener('DOMContentLoaded', function() {
  const countryInput = document.getElementById('country');
  if (!countryInput) return;

  // Comprehensive list of countries
  const countries = [
    'Afghanistan', 'Albania', 'Algeria', 'Argentina', 'Australia', 'Austria',
    'Bahrain', 'Bangladesh', 'Belgium', 'Brazil', 'Bulgaria', 'Burkina Faso',
    'Cambodia', 'Cameroon', 'Canada', 'Chile', 'China', 'Colombia', 'Costa Rica', 'Croatia', 'Czech Republic',
    'Denmark', 'Dominican Republic',
    'Ecuador', 'Egypt', 'El Salvador', 'Estonia', 'Ethiopia',
    'Finland', 'France',
    'Germany', 'Ghana', 'Greece', 'Guatemala',
    'Honduras', 'Hong Kong', 'Hungary',
    'Iceland', 'India', 'Indonesia', 'Iran', 'Iraq', 'Ireland', 'Israel', 'Italy',
    'Jamaica', 'Japan', 'Jordan',
    'Kazakhstan', 'Kenya', 'Kuwait', 'Kyrgyzstan',
    'Laos', 'Latvia', 'Lebanon', 'Libya', 'Lithuania',
    'Malaysia', 'Mali', 'Malta', 'Mexico', 'Morocco', 'Mozambique', 'Myanmar',
    'Nepal', 'Netherlands', 'New Zealand', 'Nicaragua', 'Niger', 'Nigeria', 'North Korea', 'Norway',
    'Oman',
    'Pakistan', 'Panama', 'Paraguay', 'Peru', 'Philippines', 'Poland', 'Portugal',
    'Qatar',
    'Romania', 'Russia', 'Rwanda',
    'Saudi Arabia', 'Senegal', 'Serbia', 'Singapore', 'Slovakia', 'Slovenia', 'South Africa', 'South Korea', 'Spain', 'Sri Lanka', 'Sudan', 'Sweden', 'Switzerland', 'Syria',
    'Taiwan', 'Tanzania', 'Thailand', 'Tunisia', 'Turkey',
    'Uganda', 'Ukraine', 'United Arab Emirates', 'United Kingdom', 'United States', 'Uruguay', 'Uzbekistan',
    'Venezuela', 'Vietnam',
    'Yemen',
    'Zambia', 'Zimbabwe'
  ];

  // Create dropdown container
  const dropdownContainer = document.createElement('div');
  dropdownContainer.className = 'country-dropdown';
  dropdownContainer.id = 'country-dropdown';
  countryInput.parentNode.appendChild(dropdownContainer);

  let filteredCountries = [];
  let selectedIndex = -1;

  // Filter countries based on input
  function filterCountries(searchTerm) {
    if (!searchTerm) {
      filteredCountries = [];
      dropdownContainer.innerHTML = '';
      dropdownContainer.style.display = 'none';
      return;
    }

    const term = searchTerm.toLowerCase();
    filteredCountries = countries.filter(country => 
      country.toLowerCase().startsWith(term)
    );

    if (filteredCountries.length > 0) {
      displayCountries();
    } else {
      dropdownContainer.innerHTML = '<div class="country-item no-results">No country found</div>';
      dropdownContainer.style.display = 'block';
    }
  }

  // Display filtered countries
  function displayCountries() {
    dropdownContainer.innerHTML = '';
    filteredCountries.slice(0, 10).forEach((country, index) => {
      const item = document.createElement('div');
      item.className = 'country-item';
      item.textContent = country;
      item.addEventListener('click', function() {
        countryInput.value = country;
        dropdownContainer.style.display = 'none';
        validateCountry();
      });
      dropdownContainer.appendChild(item);
    });
    dropdownContainer.style.display = 'block';
    selectedIndex = -1;
  }

  // Validate country
  function validateCountry() {
    const value = countryInput.value.trim();
    const isValid = countries.includes(value);
    
    countryInput.classList.remove('valid', 'invalid');
    
    if (value === '') {
      countryInput.classList.remove('invalid');
      return false;
    }
    
    if (isValid) {
      countryInput.classList.add('valid');
      return true;
    } else {
      countryInput.classList.add('invalid');
      return false;
    }
  }

  // Event listeners
  countryInput.addEventListener('input', function() {
    const value = this.value.trim();
    filterCountries(value);
    validateCountry();
  });

  countryInput.addEventListener('focus', function() {
    if (this.value.trim() && filteredCountries.length === 0) {
      filterCountries(this.value);
    }
  });

  countryInput.addEventListener('blur', function() {
    // Delay hiding dropdown to allow clicks
    setTimeout(function() {
      dropdownContainer.style.display = 'none';
    }, 200);
  });

  // Keyboard navigation
  countryInput.addEventListener('keydown', function(e) {
    if (filteredCountries.length === 0) return;

    if (e.key === 'ArrowDown') {
      e.preventDefault();
      selectedIndex = Math.min(selectedIndex + 1, filteredCountries.length - 1);
      highlightCountry();
    } else if (e.key === 'ArrowUp') {
      e.preventDefault();
      selectedIndex = Math.max(selectedIndex - 1, -1);
      highlightCountry();
    } else if (e.key === 'Enter' && selectedIndex >= 0) {
      e.preventDefault();
      countryInput.value = filteredCountries[selectedIndex];
      dropdownContainer.style.display = 'none';
      validateCountry();
    } else if (e.key === 'Escape') {
      dropdownContainer.style.display = 'none';
    }
  });

  function highlightCountry() {
    const items = dropdownContainer.querySelectorAll('.country-item');
    items.forEach((item, index) => {
      if (index === selectedIndex) {
        item.classList.add('highlighted');
        item.scrollIntoView({ block: 'nearest' });
      } else {
        item.classList.remove('highlighted');
      }
    });
  }

  // Click outside to close
  document.addEventListener('click', function(e) {
    if (!countryInput.contains(e.target) && !dropdownContainer.contains(e.target)) {
      dropdownContainer.style.display = 'none';
    }
  });
});
