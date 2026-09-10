import { useState } from 'react';

function StudentCard({ name, id, course, year }) {
  const [favorites, setFavorites] = useState(0);

  const handleLike = () => {
    setFavorites(favorites + 1);
  };

  return (
    <div className="student-card">
      <h3>{name}</h3>
      <p className="student-id">{id}</p>
      <div className="details">
        {course} — {year}
      </div>
      <button className="like-btn" onClick={handleLike}>
        Favorite: {favorites}
      </button>
    </div>
  );
}

export default StudentCard;