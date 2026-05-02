import { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { postJson } from '../api';
import Loader from '../components/Loader';

export default function Login({ onAuthChange }) {
  const [type, setType] = useState('user');
  const [identifier, setIdentifier] = useState('');
  const [password, setPassword] = useState('');
  const [error, setError] = useState('');
  const [loading, setLoading] = useState(false);
  const navigate = useNavigate();

  const label = type === 'admincashier' ? 'Email address' : 'Student ID';

  async function handleSubmit(event) {
    event.preventDefault();
    setError('');
    setLoading(true);

    try {
      const result = await postJson('api/login.php', {
        type,
        identifier,
        password
      });

      if (result.success) {
        onAuthChange({
          logged_in: true,
          role: result.role,
          name: result.name || null
        });
        navigate('/dashboard');
      } else {
        setError(result.message || 'Login failed');
      }
    } catch (caught) {
      setError(caught.message || 'Unable to sign in');
    } finally {
      setLoading(false);
    }
  }

  return (
    <section className="page-content">
      <div className="panel panel-card">
        <div className="panel-title">Sign in to GCST</div>
        <p className="panel-description">Use your registered student ID or admin email to access the portal.</p>

        <form className="form-grid" onSubmit={handleSubmit}>
          <label>
            <span>Account type</span>
            <select value={type} onChange={(event) => setType(event.target.value)}>
              <option value="user">Student</option>
              <option value="admincashier">Admin/Cashier</option>
            </select>
          </label>

          <label>
            <span>{label}</span>
            <input
              name="identifier"
              value={identifier}
              onChange={(event) => setIdentifier(event.target.value)}
              placeholder={label}
              required
            />
          </label>

          <label>
            <span>Password</span>
            <input
              type="password"
              name="password"
              value={password}
              onChange={(event) => setPassword(event.target.value)}
              placeholder="Enter your password"
              required
            />
          </label>

          {error && <div className="alert alert-error">{error}</div>}

          <button type="submit" className="button button-primary" disabled={loading}>
            {loading ? <Loader label="Signing in..." /> : 'Sign In'}
          </button>
        </form>
      </div>
    </section>
  );
}
