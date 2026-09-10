import { useState } from 'react';
import StudentCard from './components/StudentCard';
import './App.css';

const initialStudents = [
  { id: '2026-001', name: 'Juan Dela Cruz', course: 'BSIT', year: '2nd Year' },
  { id: '2026-002', name: 'Maria Santos', course: 'BSIT', year: '1st Year' },
  { id: '2026-003', name: 'Pedro Reyes', course: 'BSCS', year: '3rd Year' },
  { id: '2026-004', name: 'Ana Lopez', course: 'BSCS', year: '4th Year' },
  { id: '2026-005', name: 'Carlo Ramos', course: 'BSIT', year: '2nd Year' },
];

function App() {
  const [students, setStudents] = useState(initialStudents);
  const [searchTerm, setSearchTerm] = useState('');
  const [showForm, setShowForm] = useState(false);

  const [newStudent, setNewStudent] = useState({
    name: '',
    id: '',
    course: '',
    year: '',
  });

  const filteredStudents = students.filter((student) =>
    student.name.toLowerCase().includes(searchTerm.toLowerCase()) ||
    student.id.toLowerCase().includes(searchTerm.toLowerCase())
  );

  const handleInputChange = (e) => {
    setNewStudent({ ...newStudent, [e.target.name]: e.target.value });
  };

  const handleAddStudent = (e) => {
    e.preventDefault();
    if (!newStudent.name || !newStudent.id) return;

    setStudents([...students, newStudent]);
    setNewStudent({ name: '', id: '', course: '', year: '' });
    setShowForm(false);
  };

  return (
    <div className="app">
      <header className="app-header">
        <h1>Student Directory</h1>
        <p className="subtitle">University Student Records</p>
      </header>

      <div className="toolbar">
        <input
          type="text"
          className="search-bar"
          placeholder="Search by name or ID..."
          value={searchTerm}
          onChange={(e) => setSearchTerm(e.target.value)}
        />
        <button className="add-btn" onClick={() => setShowForm(!showForm)}>
          {showForm ? 'Cancel' : '+ Add Student'}
        </button>
      </div>

      {showForm && (
        <form className="add-form" onSubmit={handleAddStudent}>
          <input
            type="text"
            name="name"
            placeholder="Full Name"
            value={newStudent.name}
            onChange={handleInputChange}
            required
          />
          <input
            type="text"
            name="id"
            placeholder="Student ID (e.g. 2026-006)"
            value={newStudent.id}
            onChange={handleInputChange}
            required
          />
          <input
            type="text"
            name="course"
            placeholder="Course (e.g. BSIT)"
            value={newStudent.course}
            onChange={handleInputChange}
          />
          <input
            type="text"
            name="year"
            placeholder="Year Level (e.g. 2nd Year)"
            value={newStudent.year}
            onChange={handleInputChange}
          />
          <button type="submit" className="submit-btn">Save Student</button>
        </form>
      )}

      <div className="student-grid">
        {filteredStudents.length > 0 ? (
          filteredStudents.map((student) => (
            <StudentCard
              key={student.id}
              name={student.name}
              id={student.id}
              course={student.course}
              year={student.year}
            />
          ))
        ) : (
          <p className="no-results">No students found.</p>
        )}
      </div>
    </div>
  );
}

export default App;