function EnrollmentList({ enrollments, courses, onEdit, onDelete }) {
  function courseTitle(code) {
    const course = courses.find((c) => c.code === code);
    return course ? `${course.code} - ${course.title}` : code;
  }

  if (enrollments.length === 0) {
    return <p className="empty-state">No enrollments yet. Add one using the form.</p>;
  }

  return (
    <table className="enrollment-table">
      <thead>
        <tr>
          <th>Student</th>
          <th>Student ID</th>
          <th>Course</th>
          <th>Year Level</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        {enrollments.map((en) => (
          <tr key={en.id}>
            <td>{en.studentName}</td>
            <td>{en.studentId}</td>
            <td>{courseTitle(en.courseCode)}</td>
            <td>{en.yearLevel}</td>
            <td className="row-actions">
              <button onClick={() => onEdit(en)}>Edit</button>
              <button className="danger" onClick={() => onDelete(en.id)}>
                Delete
              </button>
            </td>
          </tr>
        ))}
      </tbody>
    </table>
  );
}

export default EnrollmentList;
