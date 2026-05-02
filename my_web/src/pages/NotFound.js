import { Link } from 'react-router-dom';

export default function NotFound() {
  return (
    <section className="page-content">
      <div className="panel panel-card centered-card">
        <div className="panel-title">Page Not Found</div>
        <p className="panel-description">The page you are looking for does not exist.</p>
        <Link to="/" className="button button-secondary">Return Home</Link>
      </div>
    </section>
  );
}
