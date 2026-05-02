import { useEffect, useState } from 'react';
import { HashRouter as Router, Routes, Route, NavLink, Navigate } from 'react-router-dom';
import { fetchJson, postJson } from './api';
import './App.css';
import Dashboard from './pages/Dashboard';
import Products from './pages/Products';
import Register from './pages/Register';
import Profile from './pages/Profile';
import Login from './pages/Login';
import NotFound from './pages/NotFound';
import Loader from './components/Loader';

function App() {
  const [session, setSession] = useState({ logged_in: false, role: null, name: null, student_id: null, admin_id: null });
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');

  useEffect(() => {
    fetchJson('get_user.php')
      .then((data) => setSession({
        logged_in: data.logged_in ?? false,
        role: data.role ?? null,
        name: data.name ?? null,
        student_id: data.student_id ?? null,
        admin_id: data.admin_id ?? null
      }))
      .catch(() => setSession((current) => ({ ...current, logged_in: false })))
      .finally(() => setLoading(false));
  }, []);

  async function handleLogout() {
    try {
      await postJson('api/logout.php', {});
    } catch (caught) {
      console.warn('Logout failed', caught);
    } finally {
      setSession({ logged_in: false, role: null, name: null, student_id: null, admin_id: null });
    }
  }

  function handleAuthChange(data) {
    setSession((current) => ({ ...current, ...data, logged_in: true }));
  }

  const navItems = [
    { to: '/dashboard', label: 'Dashboard', authRequired: true },
    { to: '/products', label: 'Products', authRequired: false },
    { to: '/register', label: 'Register', authRequired: false },
    { to: '/profile', label: 'Profile', authRequired: true }
  ];

  return (
    <Router>
      <div className="app-shell">
        <aside className="app-sidebar">
          <div className="brand">
            <span className="brand-mark">GCST</span>
            <div>
              <strong>Track System</strong>
              <p>React frontend</p>
            </div>
          </div>

          <nav className="app-nav">
            {navItems.map((item) => {
              if (item.authRequired && !session.logged_in) return null;
              return (
                <NavLink key={item.to} to={item.to} className={({ isActive }) => `nav-link ${isActive ? 'active' : ''}`}>
                  {item.label}
                </NavLink>
              );
            })}
            {!session.logged_in && (
              <NavLink to="/login" className={({ isActive }) => `nav-link ${isActive ? 'active' : ''}`}>
                Login
              </NavLink>
            )}
          </nav>

          <div className="sidebar-footer">
            {session.logged_in ? (
              <button className="button button-secondary" type="button" onClick={handleLogout}>
                Logout
              </button>
            ) : (
              <span className="status-chip">Guest mode</span>
            )}
          </div>
        </aside>

        <main className="app-main">
          <header className="app-header">
            <div>
              <h1>GCST Track System</h1>
              <p>{session.logged_in ? `Welcome back, ${session.name || 'user'}` : 'Sign in to access protected pages.'}</p>
            </div>
            <div className="header-meta">
              <span className="status-chip">{session.role ? session.role.toUpperCase() : 'GUEST'}</span>
            </div>
          </header>

          <section className="app-content">
            {loading ? (
              <Loader label="Checking session..." />
            ) : (
              <Routes>
                <Route path="/" element={<Navigate to={session.logged_in ? '/dashboard' : '/login'} replace />} />
                <Route path="/login" element={<Login onAuthChange={handleAuthChange} />} />
                <Route path="/dashboard" element={session.logged_in ? <Dashboard /> : <Navigate to="/login" replace />} />
                <Route path="/products" element={<Products />} />
                <Route path="/register" element={<Register />} />
                <Route path="/profile" element={<Profile session={session} />} />
                <Route path="*" element={<NotFound />} />
              </Routes>
            )}
            {error && <div className="alert alert-error">{error}</div>}
          </section>
        </main>
      </div>
    </Router>
  );
}

export default App;
