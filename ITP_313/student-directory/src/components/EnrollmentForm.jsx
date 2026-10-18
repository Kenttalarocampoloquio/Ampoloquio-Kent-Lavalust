import { useState, useEffect } from "react";

const emptyForm = {
  studentName: "",
  studentId: "",
  courseCode: "",
  yearLevel: "",
};

function EnrollmentForm({ courses, editingEnrollment, onSubmit, onCancel }) {
  const [form, setForm] = useState(emptyForm);

  useEffect(() => {
    setForm(editingEnrollment ? { ...editingEnrollment } : emptyForm);
  }, [editingEnrollment]);

  const availableCourses = courses.filter((c) => c.yearLevel === form.yearLevel);

  function handleChange(e) {
    const { name, value } = e.target;
    setForm((prev) => {
      if (name === "yearLevel") {
        return { ...prev, yearLevel: value, courseCode: "" };
      }
      return { ...prev, [name]: value };
    });
  }

  function handleSubmit(e) {
    e.preventDefault();
    if (!form.studentName.trim() || !form.studentId.trim() || !form.courseCode) {
      alert("Please fill in student name, student ID, and course.");
      return;
    }
    onSubmit(form);
    setForm(emptyForm);
  }

  return (
    <form className="enrollment-form" onSubmit={handleSubmit}>
      <h2>{editingEnrollment ? "Edit Enrollment" : "New Enrollment"}</h2>

      <label>
        Student Name
        <input
          name="studentName"
          value={form.studentName}
          onChange={handleChange}
          placeholder="Juan Dela Cruz"
        />
      </label>

      <label>
        Student ID
        <input
          name="studentId"
          value={form.studentId}
          onChange={handleChange}
          placeholder="2023-00123"
        />
      </label>

      <label>
        Year Level
        <select name="yearLevel" value={form.yearLevel} onChange={handleChange}>
          <option>1st Year</option>
          <option>2nd Year</option>
          <option>3rd Year</option>
          <option>4th Year</option>
        </select>
      </label>

      <label>
        Course
        <select name="courseCode" value={form.courseCode} onChange={handleChange}>
          <option value="">-- Select a course --</option>
          {availableCourses.map((c) => (
            <option key={c.code} value={c.code}>
              {c.code} - {c.title}
            </option>
          ))}
        </select>
      </label>

      <div className="form-actions">
        <button type="submit">
          {editingEnrollment ? "Save Changes" : "Enroll Student"}
        </button>
        {editingEnrollment && (
          <button type="button" className="secondary" onClick={onCancel}>
            Cancel
          </button>
        )}
      </div>
    </form>
  );
}

export default EnrollmentForm;