import { useEffect, useState } from 'react';
import { fetchJson } from '../api';
import Loader from '../components/Loader';

const metricDefinitions = [
  { field: 'total_sales_today', title: 'Sales Today', accent: 'primary' },
  { field: 'total_transactions', title: 'Transactions', accent: 'success' },
  { field: 'books_sold', title: 'Books Sold', accent: 'accent' },
  { field: 'total_inventory', title: 'Inventory', accent: 'neutral' },
  { field: 'pending_queue', title: 'Pending Queue', accent: 'warning' },
  { field: 'books_rented', title: 'Active Rentals', accent: 'info' }
];

export default function Dashboard() {
  const [dashboard, setDashboard] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');

  useEffect(() => {
    setLoading(true);
    fetchJson('get_admincashier_dashboard.php')
      .then((data) => setDashboard(data))
      .catch((caught) => setError(caught.message || 'Unable to load dashboard.'))
      .finally(() => setLoading(false));
  }, []);

  if (loading) return <Loader label="Loading dashboard..." />;
  if (error) return <div className="alert alert-error">{error}</div>;

  return (
    <section className="page-content">
      <div className="hero-panel">
        <div>
          <h1>Dashboard</h1>
          <p>Overview of the most important system metrics from the backend.</p>
        </div>
      </div>

      <div className="grid-card-list">
        {metricDefinitions.map((metric) => (
          <article key={metric.field} className={`stat-card stat-${metric.accent}`}>
            <span className="stat-label">{metric.title}</span>
            <strong className="stat-value">{dashboard[metric.field] ?? 0}</strong>
          </article>
        ))}
      </div>
    </section>
  );
}
