const { useState, useEffect } = React;
const { HashRouter, Routes, Route, Link, useNavigate, Navigate } = ReactRouterDOM;

function LoadingSpinner() {
  return React.createElement('div', { className: 'loading-state' },
    React.createElement('div', { className: 'spinner' }),
    React.createElement('span', null, 'Loading data...')
  );
}

function Card({ title, value, subtitle, variant }) {
  return React.createElement('div', { className: `dashboard-card ${variant || ''}` },
    React.createElement('h3', null, title),
    React.createElement('p', { className: 'dashboard-value' }, value),
    React.createElement('p', { className: 'dashboard-subtitle' }, subtitle)
  );
}

function Dashboard() {
  const [data, setData] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');

  useEffect(() => {
    fetch('/GCST_Track_System/actions/get_admincashier_dashboard.php', { cache: 'no-store' })
      .then((res) => {
        if (!res.ok) throw new Error('Unable to load dashboard data');
        return res.json();
      })
      .then((payload) => {
        setData(payload);
      })
      .catch((err) => {
        setError(err.message);
      })
      .finally(() => {
        setLoading(false);
      });
  }, []);

  if (loading) return React.createElement(LoadingSpinner, null);
  if (error) return React.createElement('div', { className: 'error-card' }, error);

  return React.createElement('section', { className: 'page-grid' },
    React.createElement('div', { className: 'hero-panel' },
      React.createElement('h1', null, 'React Admin Dashboard'),
      React.createElement('p', null, 'A modern frontend overlay for the existing GCST backend.'),
      React.createElement('div', { className: 'hero-buttons' },
        React.createElement(Link, { to: '/products', className: 'button secondary' }, 'View Products'),
        React.createElement(Link, { to: '/profile', className: 'button primary' }, 'Profile')
      )
    ),
    React.createElement('div', { className: 'metrics-grid' },
      React.createElement(Card, {
        title: 'Total Sales',
        value: `₱ ${data.total_sales_today ?? data.total_sales ?? 0}`,
        subtitle: 'Based on the latest summary',
        variant: 'primary'
      }),
      React.createElement(Card, {
        title: 'Transactions',
        value: `${data.total_transactions ?? 0}`,
        subtitle: 'Processed orders today',
        variant: 'success'
      }),
      React.createElement(Card, {
        title: 'Items Sold',
        value: `${data.books_sold ?? data.books_sold ?? 0}`,
        subtitle: 'Total book items sold',
        variant: 'accent'
      })
    )
  );
}

function ProductsTable() {
  const [products, setProducts] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');

  useEffect(() => {
    fetch('/GCST_Track_System/actions/get_products.php', { cache: 'no-store' })
      .then((res) => {
        if (!res.ok) throw new Error('Failed to load products');
        return res.json();
      })
      .then((payload) => setProducts(payload))
      .catch((err) => setError(err.message))
      .finally(() => setLoading(false));
  }, []);

  if (loading) return React.createElement(LoadingSpinner, null);
  if (error) return React.createElement('div', { className: 'error-card' }, error);

  return React.createElement('section', { className: 'page-section' },
    React.createElement('h1', null, 'Products'),
    React.createElement('div', { className: 'table-container' },
      React.createElement('table', null,
        React.createElement('thead', null,
          React.createElement('tr', null,
            React.createElement('th', null, 'ID'),
            React.createElement('th', null, 'Name'),
            React.createElement('th', null, 'Category'),
            React.createElement('th', null, 'Price'),
            React.createElement('th', null, 'Status')
          )
        ),
        React.createElement('tbody', null,
          products.map((item) => React.createElement('tr', { key: item.product_id },
            React.createElement('td', null, item.product_id),
            React.createElement('td', null, item.product_name),
            React.createElement('td', null, item.product_category || 'N/A'),
            React.createElement('td', null, `₱ ${item.product_price ?? '0.00'}`),
            React.createElement('td', null, item.product_status || 'Available')
          ))
        )
      )
    )
  );
}

function UserSignup() {
  const [form, setForm] = useState({
    student_id: '',
    last_name: '',
    first_name: '',
    middle_name: '',
    email: '',
    password: '',
    confirm_password: '',
    sex: '',
    course: '',
    year_section: '',
    contact_number: '',
    address: ''
  });
  const [status, setStatus] = useState({ loading: false, message: '', error: false });

  function handleChange(event) {
    setForm({ ...form, [event.target.name]: event.target.value });
  }

  function handleSubmit(event) {
    event.preventDefault();
    if (form.password !== form.confirm_password) {
      setStatus({ loading: false, message: 'Passwords do not match.', error: true });
      return;
    }

    setStatus({ loading: true, message: 'Registering account...', error: false });
    fetch('/GCST_Track_System/actions/api/register_user.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(form)
    })
      .then((res) => res.json())
      .then((result) => {
        setStatus({ loading: false, message: result.message || 'Registration complete.', error: !result.success });
        if (result.success) {
          setForm({
            student_id: '', last_name: '', first_name: '', middle_name: '', email: '',
            password: '', confirm_password: '', sex: '', course: '', year_section: '', contact_number: '', address: ''
          });
        }
      })
      .catch((err) => {
        setStatus({ loading: false, message: err.message || 'Registration failed.', error: true });
      });
  }

  return React.createElement('section', { className: 'page-section' },
    React.createElement('h1', null, 'Student Signup Form'),
    React.createElement('form', { className: 'form-grid', onSubmit: handleSubmit },
      ['student_id', 'first_name', 'last_name', 'middle_name', 'email', 'sex', 'course', 'year_section', 'contact_number', 'address', 'password', 'confirm_password'].map((key) =>
        React.createElement('label', { key },
          React.createElement('span', null, key.replace(/_/g, ' ').replace(/\w/g, (match) => match.toUpperCase())),
          React.createElement(key.includes('password') ? 'input' : 'input', {
            type: key.includes('password') ? 'password' : 'text',
            name: key,
            value: form[key],
            onChange: handleChange,
            required: key !== 'middle_name',
            autoComplete: 'off',
            placeholder: key.includes('password') ? '••••••••' : `Enter ${key.replace(/_/g, ' ')}`
          })
        )
      ),
      React.createElement('button', { type: 'submit', className: 'button primary' }, status.loading ? 'Saving...' : 'Create Account'),
      status.message && React.createElement('div', { className: `form-status ${status.error ? 'error' : 'success'}` }, status.message)
    )
  );
}

function Profile() {
  const [session, setSession] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');
  const navigate = useNavigate();

  useEffect(() => {
    fetch('/GCST_Track_System/actions/get_user.php', { cache: 'no-store' })
      .then((res) => res.json())
      .then((payload) => setSession(payload))
      .catch((err) => setError(err.message))
      .finally(() => setLoading(false));
  }, []);

  function handleLogout() {
    fetch('/GCST_Track_System/actions/api/logout.php', { method: 'POST' })
      .then(() => navigate('/'))
      .catch(() => setError('Logout failed.'));
  }

  if (loading) return React.createElement(LoadingSpinner, null);
  if (error) return React.createElement('div', { className: 'error-card' }, error);

  return React.createElement('section', { className: 'page-section' },
    React.createElement('h1', null, 'My Session Profile'),
    React.createElement('div', { className: 'profile-card' },
      React.createElement('p', null, React.createElement('strong', null, 'Name:'), ' ', session.name || 'Guest'),
      React.createElement('p', null, React.createElement('strong', null, 'Role:'), ' ', session.role || 'Guest'),
      React.createElement('p', null, React.createElement('strong', null, 'User ID:'), ' ', session.student_id || session.admin_id || 'None'),
      React.createElement('button', { className: 'button secondary', onClick: handleLogout }, 'Logout')
    )
  );
}

function App() {
  return React.createElement(HashRouter, null,
    React.createElement('div', { className: 'react-shell' },
      React.createElement('aside', { className: 'react-sidebar' },
        React.createElement('div', { className: 'brand' },
          React.createElement('img', { src: '/GCST_Track_System/assets/images/icons/granbylogo.png', alt: 'GCST Logo' }),
          React.createElement('h2', null, 'GCST React')
        ),
        React.createElement('nav', { className: 'react-nav' },
          React.createElement(Link, { to: '/', className: 'react-link' }, 'Dashboard'),
          React.createElement(Link, { to: '/products', className: 'react-link' }, 'Products'),
          React.createElement(Link, { to: '/signup', className: 'react-link' }, 'Signup Form'),
          React.createElement(Link, { to: '/profile', className: 'react-link' }, 'Profile')
        )
      ),
      React.createElement('main', { className: 'react-main' },
        React.createElement(Routes, null,
          React.createElement(Route, { path: '/', element: React.createElement(Dashboard, null) }),
          React.createElement(Route, { path: '/products', element: React.createElement(ProductsTable, null) }),
          React.createElement(Route, { path: '/signup', element: React.createElement(UserSignup, null) }),
          React.createElement(Route, { path: '/profile', element: React.createElement(Profile, null) }),
          React.createElement(Route, { path: '*', element: React.createElement(Navigate, { to: '/' }) })
        )
      )
    )
  );
}

ReactDOM.createRoot(document.getElementById('react-root')).render(React.createElement(App, null));
