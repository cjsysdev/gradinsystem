<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Interactive Database Design Worksheet</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background: #f4f6f8;
      margin: 0;
      padding: 20px;
    }
    .container {
      max-width: 1000px;
      margin: auto;
      background: #fff;
      padding: 25px;
      border-radius: 8px;
      box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    }
    h1, h2 {
      color: #2c3e50;
    }
    .scenario {
      background: #eef3f7;
      padding: 15px;
      border-left: 5px solid #3498db;
      margin-bottom: 20px;
    }
    label {
      font-weight: bold;
      display: block;
      margin-top: 10px;
    }
    input, textarea, select {
      width: 100%;
      padding: 8px;
      margin-top: 5px;
      border-radius: 4px;
      border: 1px solid #ccc;
    }
    textarea {
      resize: vertical;
    }
    .entity-block {
      background: #fafafa;
      border: 1px dashed #ccc;
      padding: 15px;
      margin-top: 15px;
      border-radius: 6px;
    }
    .row {
      display: flex;
      gap: 10px;
      flex-wrap: wrap;
    }
    .row > div {
      flex: 1;
      min-width: 200px;
    }
    button {
      background: #3498db;
      color: white;
      border: none;
      padding: 10px 16px;
      border-radius: 5px;
      cursor: pointer;
      margin-top: 15px;
    }
    button:hover {
      background: #2980b9;
    }
    .footer-note {
      margin-top: 30px;
      font-size: 0.9em;
      color: #555;
      text-align: center;
    }
  </style>
</head>
<body>
  <div class="container">
    <h1>Database Design Worksheet (Interactive)</h1>

    <div class="scenario">
      <h2>Scenario</h2>
      <p>
        A public library wants to manage its collection and membership more efficiently. The system should track books, authors, and genres. Each book can have multiple authors, and each author can write multiple books. Members can borrow multiple books, but each book can only be borrowed by one member at a time. The library also needs to track borrowing dates, due dates, and fines for late returns.
      </p>
    </div>

    <h2>A. Identify Entities</h2>
    <div class="row">
      <div><input placeholder="Entity 1"></div>
      <div><input placeholder="Entity 2"></div>
      <div><input placeholder="Entity 3"></div>
      <div><input placeholder="Entity 4"></div>
      <div><input placeholder="Entity 5"></div>
    </div>

    <h2>B. Define Attributes</h2>
    <p>For each entity, list at least 3 attributes and mark PK or FK.</p>

    <div class="entity-block">
      <label>Entity Name</label>
      <input placeholder="e.g., Book">
      <label>Attribute 1</label>
      <input placeholder="book_id (PK)">
      <label>Attribute 2</label>
      <input placeholder="title">
      <label>Attribute 3</label>
      <input placeholder="genre_id (FK)">
    </div>

    <button onclick="addEntity()">+ Add Another Entity</button>

    <h2>C. Relationships</h2>
    <div class="row">
      <div>
        <input placeholder="Entity A">
      </div>
      <div>
        <input placeholder="Entity B">
      </div>
      <div>
        <select>
          <option>1-to-1</option>
          <option>1-to-Many</option>
          <option>Many-to-Many</option>
        </select>
      </div>
    </div>

    <button onclick="addRelationship()">+ Add Relationship</button>

    <!-- <h2>D. Draw ERD</h2>
    <p>Draw your ERD using paper or an ERD tool, then describe it below.</p>
    <textarea rows="4" placeholder="Brief description of your ERD diagram..."></textarea>

    <h2>E. Explanation</h2>
    <textarea rows="4" placeholder="Explain your database design in 2–3 sentences..."></textarea> -->

    <div class="footer-note">
      💡 Tip: Focus on entities, primary keys, foreign keys, and relationship cardinality.
    </div>
  </div>

  <script>
    function addEntity() {
      const block = document.createElement('div');
      block.className = 'entity-block';
      block.innerHTML = `
        <label>Entity Name</label>
        <input placeholder="e.g., Member">
        <label>Attribute 1</label>
        <input placeholder="id (PK)">
        <label>Attribute 2</label>
        <input placeholder="name">
        <label>Attribute 3</label>
        <input placeholder="email">
      `;
      document.querySelector('.container').insertBefore(block, document.querySelector('h2:nth-of-type(3)'));
    }

    function addRelationship() {
      const row = document.createElement('div');
      row.className = 'row';
      row.innerHTML = `
        <div><input placeholder="Entity A"></div>
        <div><input placeholder="Entity B"></div>
        <div>
          <select>
            <option>1-to-1</option>
            <option>1-to-Many</option>
            <option>Many-to-Many</option>
          </select>
        </div>
      `;
      document.querySelector('h2:nth-of-type(3)').after(row);
    }

    
  </script>
</body>
</html>
