import { useState, useEffect } from "react";
import EnrollmentForm from "./components/EnrollmentForm";
import EnrollmentList from "./components/EnrollmentList";
import "./App.css";

function App() {
  const [courses, setCourses] = useState([]);
  const [enrollments, setEnrollments] = useState([]);
  const [editingEnrollment, setEditingEnrollment] = useState(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    fetch("/Courses.json")
      .then((res) => res.json())
      .then((data) => {
        setCourses(data);
        setLoading(false);
      })
      .catch((err) => {
        console.error("Failed to load courses.json", err);
        setLoading(false);
      });
  }, []);

  function handleSubmit(formData) {
    if (editingEnrollment) {
      setEnrollments((prev) =>
        prev.map((en) =>
          en.id === editingEnrollment.id ? { ...formData, id: en.id } : en
        )
      );
      setEditingEnrollment(null);
    } else {
      setEnrollments((prev) => [...prev, { ...formData, id: Date.now() }]);
    }
  }

  function handleEdit(enrollment) {
    setEditingEnrollment(enrollment);
  }

  function handleCancelEdit() {
    setEditingEnrollment(null);
  }

  function handleDelete(id) {
    if (editingEnrollment?.id === id) setEditingEnrollment(null);
    setEnrollments((prev) => prev.filter((en) => en.id !== id));
  }

  if (loading) {
    return <p className="loading">Loading course offerings...</p>;
  }

  return (
    <div className="app">
      <header>
        <h1>Course Enrollment Manager</h1>
        <p>{enrollments.length} student(s) enrolled</p>
      </header>

      <EnrollmentForm
        courses={courses}
        editingEnrollment={editingEnrollment}
        onSubmit={handleSubmit}
        onCancel={handleCancelEdit}
      />

      <EnrollmentList
        enrollments={enrollments}
        courses={courses}
        onEdit={handleEdit}
        onDelete={handleDelete}
      />
    </div>
  );
}

export default App;