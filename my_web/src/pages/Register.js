import { useState } from 'react';
import { postJson } from '../api';
import Loader from '../components/Loader';

const initialForm = {
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
};

export default function Register() {
  const [form, setForm] = useState(initialForm);
  const [status, setStatus] = useState({ loading: false, message: '', type: '' });

  async function handleSubmit(event) {
    event.preventDefault();
    setStatus({ loading: true, message: '', type: '' });

    if (form.password !== form.confirm_password) {
      setStatus({ loading: false, message: 'Passwords do not match.', type: 'error' });
      return;
    }

    try {
      const response = await postJson('api/register_user.php', {
        ...form
      });

      setStatus({
        loading: false,
        message: response.message || 'Registration submitted successfully.',
        type: response.success ? 'success' : 'error'
      });

      if (response.success) {
        setForm(initialForm);
      }
    } catch (caught) {
      setStatus({ loading: false, message: caught.message || 'Registration request failed.', type: 'error' });
    }
  }

  function updateField(field, value) {
    setForm((current) => ({ ...current, [field]: value }));
  }

  return (
    <section className="page-content">
      <div className="panel panel-card">
        <div className="panel-title">Student Signup</div>
        <p className="panel-description">Register a new student account for GCST Track System.</p>

        <form className="form-grid" onSubmit={handleSubmit}>
          {['student_id', 'last_name', 'first_name', 'middle_name', 'email', 'sex', 'course', 'year_section', 'contact_number', 'address'].map((field) => (
            <label key={field}>
              <span>{field.replace('_', ' ').replace(/\b\w/g, (letter) => letter.toUpperCase())}</span>
              <input
                name={field}
                value={form[field]}
                onChange={(event) => updateField(field, event.target.value)}
                placeholder={`Enter ${field.replace('_', ' ')}`}
                required={field !== 'middle_name'}
                type={field === 'email' ? 'email' : 'text'}
              />
            </label>
          ))}

          <label>
            <span>Password</span>
            <input
              type="password"
              name="password"
              value={form.password}
              onChange={(event) => updateField('password', event.target.value)}
              placeholder="Create a password"
              required
            />
          </label>
          <label>
            <span>Confirm Password</span>
            <input
              type="password"
              name="confirm_password"
              value={form.confirm_password}
              onChange={(event) => updateField('confirm_password', event.target.value)}
              placeholder="Confirm your password"
              required
            />
          </label>

          {status.message && (
            <div className={`alert ${status.type === 'success' ? 'alert-success' : 'alert-error'}`}>
              {status.message}
            </div>
          )}

          <button type="submit" className="button button-primary" disabled={status.loading}>
            {status.loading ? <Loader label="Registering..." /> : 'Create account'}
          </button>
        </form>
      </div>
    </section>
  );
}
