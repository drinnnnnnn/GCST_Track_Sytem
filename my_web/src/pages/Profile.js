export default function Profile({ session }) {
  if (!session.logged_in) {
    return (
      <section className="page-content">
        <div className="panel panel-card">
          <div className="panel-title">Profile</div>
          <div className="alert alert-info">Please sign in to view your profile details.</div>
        </div>
      </section>
    );
  }

  return (
    <section className="page-content">
      <div className="panel panel-card">
        <div className="panel-title">My Profile</div>
        <p className="panel-description">Session details retrieved from the backend.</p>
        <div className="profile-grid">
          <div>
            <span>Name</span>
            <strong>{session.name || 'Unknown'}</strong>
          </div>
          <div>
            <span>Role</span>
            <strong>{session.role || 'Guest'}</strong>
          </div>
          <div>
            <span>Student ID</span>
            <strong>{session.student_id || 'N/A'}</strong>
          </div>
          <div>
            <span>Admin ID</span>
            <strong>{session.admin_id || 'N/A'}</strong>
          </div>
        </div>
      </div>
    </section>
  );
}
