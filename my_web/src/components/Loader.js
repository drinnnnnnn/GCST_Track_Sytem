import './Loader.css';

export default function Loader({ label = 'Loading...' }) {
  return (
    <div className="loader-block">
      <div className="loader-ring" />
      <span>{label}</span>
    </div>
  );
}
