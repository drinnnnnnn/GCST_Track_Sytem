import { useEffect, useState } from 'react';
import { fetchJson } from '../api';
import Loader from '../components/Loader';

export default function Products() {
  const [products, setProducts] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');

  useEffect(() => {
    fetchJson('get_products.php')
      .then((data) => setProducts(Array.isArray(data) ? data : []))
      .catch((caught) => setError(caught.message || 'Failed to load products'))
      .finally(() => setLoading(false));
  }, []);

  if (loading) return <Loader label="Loading products..." />;
  if (error) return <div className="alert alert-error">{error}</div>;

  return (
    <section className="page-content">
      <div className="panel panel-card">
        <div className="panel-title">Product Catalog</div>
        <p className="panel-description">Review available book products pulled directly from the PHP backend.</p>
        <div className="table-scroll">
          <table className="data-table">
            <thead>
              <tr>
                <th>ID</th>
                <th>Title</th>
                <th>Category</th>
                <th>Author</th>
                <th>Price</th>
              </tr>
            </thead>
            <tbody>
              {products.length === 0 ? (
                <tr>
                  <td colSpan="5">No products found.</td>
                </tr>
              ) : (
                products.map((item) => (
                  <tr key={item.product_id || `${item.product_name}-${item.product_price}`}>
                    <td>{item.product_id}</td>
                    <td>{item.product_name}</td>
                    <td>{item.product_category || 'N/A'}</td>
                    <td>{item.product_author || 'Unknown'}</td>
                    <td>₱ {item.product_price ?? '0.00'}</td>
                  </tr>
                ))
              )}
            </tbody>
          </table>
        </div>
      </div>
    </section>
  );
}
