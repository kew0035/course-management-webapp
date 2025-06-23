<template>
  <div>
    <header class="dashboard-header">
      <div class="header-title">Academic Advisor Dashboard</div>
      <button class="logout-btn" @click="handleLogout" title="Logout">🔓 Logout
</button>
    </header>
    <div class="dashboard-container">
      <h2>Welcome, {{ advisorName }}</h2>

      <section class="advisees-section">
        <h3>Your Advisees</h3>
        <table class="advisees-table">
          <thead>
            <tr>
              <th>#</th>
              <th>Matric No</th>
              <th>Name</th>
              <th>GPA</th>
              <th>Status</th>
              <th>Private Notes</th>
              <th>Details</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(student, index) in advisees" :key="student.matric_no" :class="{ atRisk: student.gpa < 2.0 }">
               <td>{{ index + 1 }}</td>
              <td>{{ student.matric_no }}</td>
              <td>{{ student.stud_name }}</td>
              <td>{{ student.gpa != null ? Number(student.gpa).toFixed(2) : 'N/A' }}</td>
              <td>
                <span v-if="student.gpa < 2.0" class="risk-label">At Risk</span>
                <span v-else>Good</span>
              </td>
              <td>
                <button @click="viewStudentNotes(student)">Notes</button>
              </td>
              <td>
                <button @click="viewStudent(student)">View</button>
              </td>
            </tr>
          </tbody>
        </table>
      </section>

      <section class="export-section">
        <button @click="exportReport" class="export-btn">Export Consultation Report</button>
      </section>

      <!-- Modal for student detail -->
      <div v-if="selectedStudent" class="modal-overlay" @click.self="selectedStudent = null">
        <div class="modal-content">
          <h2 class="modal-title">🎓 Student Details</h2>

          <div class="student-info-row">
            <div><strong>Name:</strong> {{ selectedStudent.stud_name }}</div>
            <div><strong>Matric No:</strong> {{ selectedStudent.matric_no }}</div>
          </div>

          <h3 class="section-title">📚 Courses & Marks</h3>

          <div v-for="course in selectedStudentCourses" :key="course.course_name" class="course-card">
            <h4 class="course-name">{{ course.course_name }}</h4>

            <table class="course-table">
              <thead>
                <tr>
                  <th>Component</th>
                  <th class="score-cell">Score</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(score, comp) in course.components" :key="comp">
                  <td>{{ comp }}</td>
                  <td class="score-cell">{{ Number(score).toFixed(2) }}</td>
                </tr>
                <tr>
                  <td><strong>Final Exam</strong></td>
                  <td class="score-cell">{{ course.final_exam_score ?? '-' }}</td>
                </tr>
                <tr class="highlight-row">
                  <td><strong>Total</strong></td>
                  <td class="score-cell"><strong>{{ Number(course.total_score).toFixed(2) ?? '-' }}</strong></td>
                </tr>
                <tr class="highlight-row">
                  <td><strong>Grade</strong></td>
                  <td :class="['grade', getGradeClass(course.grade)]">
                    {{ course.grade ?? '-' }}
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <div class="modal-footer">
            <button @click="selectedStudent = null" class="close-btn">Close</button>
          </div>
        </div>
      
      </div>
      <div v-if="showNotesModal" class="modal-overlay" @click.self="closeNotesModal">
        <div class="modal-content">
          <h3 class="modal-title">📝 Notes for {{ noteStudent?.stud_name }}</h3>

          <div class="notes-list">
            <div v-for="n in studentNotes" :key="n.created_at" class="note-item">
              <div class="note-date">{{ formatDate(n.created_at) }}</div>
              <div class="note-text">{{ n.note }}</div>
            </div>
          </div>

          <div class="note-input-section">
            <textarea v-model="newNote" placeholder="Write a new note..."></textarea>
            <button @click="submitNewNote" class="add-note-btn">Add Note</button>
          </div>

          <div class="modal-footer">
            <button @click="closeNotesModal" class="close-btn">Close</button>
          </div>
        </div>
      </div>
      <div v-if="showToast" :class="['toast', toastType]">
        {{ toastMessage }}
      </div>
      </div>
  </div>
</template>


<script setup>
  import { ref, onMounted } from 'vue';

  const advisorName = ref('');
  const advisees = ref([]);
  const selectedStudent = ref(null);
  const selectedStudentCourses = ref([]);
  const componentHeaders = ref([]);
  const toastType = ref("");
  const toastMessage = ref("");
  const showToast = ref(false);
  const showNotesModal = ref(false);
  const studentNotes = ref([]);
  const newNote = ref('');
  const noteStudent = ref(null);


onMounted(async () => {
  try {
    // 获取 advisor name
    const profileRes = await fetch('http://localhost:8080/advisor/profile', {
      credentials: 'include'
    });

    if (profileRes.ok) {
      const profileData = await profileRes.json();
       advisorName.value = profileData.adv_name || 'Advisor'; // fallback if null
    } else {
      console.warn('Failed to fetch advisor profile.');
    }

    // 获取 advisees 列表
    const res = await fetch('http://localhost:8080/advisor/advisees', {
      method: 'GET',
      credentials: 'include'
    });

    if (!res.ok) {
      const resText = await res.text();
      console.error('Backend response:', resText);
      throw new Error(`HTTP error! Status: ${res.status}`);
    }

    const data = await res.json();
    advisees.value = data;

  } catch (error) {
    console.error('Initialization error:', error);
    alert('Failed to load advisor dashboard.');
  }
});


const isExporting = ref(false);

function exportReport() {
  isExporting.value = true;
  window.open('http://localhost:8080/advisor/export', '_blank');
  setTimeout(() => {
    isExporting.value = false;
    showSuccessToast('Report exported successfully.');
  }, 1000);
}


  function getGradeClass(grade) {
    if (!grade) return 'grade grade-none';

    const gradeUpper = grade.toUpperCase();

    if (gradeUpper.startsWith('A')) return 'grade grade-a';
    if (gradeUpper.startsWith('B')) return 'grade grade-b';
    if (gradeUpper.startsWith('C')) return 'grade grade-c';
    if (['D', 'F'].some(g => gradeUpper.startsWith(g))) return 'grade grade-f';

    return 'grade'; // fallback
  }

  async function viewStudent(student) {
    try {
      const res = await fetch(`http://localhost:8080/advisor/student/${student.stud_id}`, {
        method: 'GET',
        credentials: 'include'
      });

      if (!res.ok) throw new Error('Failed to fetch student detail');

      const data = await res.json(); 
      selectedStudent.value = {
        ...student
      };

      selectedStudentCourses.value = [];
      componentHeaders.value = [];

      const compSet = new Set();

      data.courses.forEach(course => {
        if (course.components) {
          Object.keys(course.components).forEach(comp => compSet.add(comp));
        }
      });

      selectedStudentCourses.value = data.courses;
      componentHeaders.value = Array.from(compSet);

    } catch (err) {
      alert('Failed to load student detail');
      console.error(err);
    }
  }


  function closeNotesModal() {
    showNotesModal.value = false;
    studentNotes.value = [];
    newNote.value = '';
    noteStudent.value = null;
  }

  async function viewStudentNotes(student) {
    noteStudent.value = student;
    try {
      const res = await fetch(`http://localhost:8080/advisor/student/${student.stud_id}/notes`, {
        credentials: 'include'
      });
      const data = await res.json();
      studentNotes.value = data;
      showNotesModal.value = true;
    } catch (e) {
      alert('Failed to load notes');
      console.error(e);
    }
  }

  async function submitNewNote() {
    if (!newNote.value.trim()) return;

    try {
      const res = await fetch(`http://localhost:8080/advisor/student/${noteStudent.value.stud_id}/note`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        credentials: 'include',
        body: JSON.stringify({ note: newNote.value })
      });

      if (!res.ok) throw new Error('Failed to save note');

      await res.json();
      await viewStudentNotes(noteStudent.value); // refresh notes
      newNote.value = '';
      showSuccessToast('Note added successfully');
    } catch (err) {
      showErrorToast('Failed to add note');
      console.error(err);
    }
  }

 function showSuccessToast(message) {
    toastType.value = "success";
    toastMessage.value = message;
    showToast.value = true;
    setTimeout(() => showToast.value = false, 3000);
  }

  function showErrorToast(message) {
    toastType.value = "error";
    toastMessage.value = message;
    showToast.value = true;
    setTimeout(() => showToast.value = false, 3000);
  }

  function formatDate(str) {
    const d = new Date(str);
    return d.toLocaleString();
  }



  function handleLogout() {
    fetch('http://localhost:8080/logout', {
      method: 'POST',
      credentials: 'include'
    }).then(() => {
      window.location.href = '/'; // shared login page
    }).catch(err => {
      console.error('Logout failed:', err);
    });
  }
</script>



<style scoped>
.dashboard-container {
  max-width: 900px;
  margin: 20px auto;
  background: white;
  padding: 20px;
  border-radius: 8px;
  font-family: Arial, sans-serif;
  box-shadow: 0 0 10px #ccc;
}

h2,
h3 {
  color: #2c3e50;
}

body, .dashboard-container {
  color: #2c3e50;
  font-size: 16px;
}

.dashboard-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  background-color: #1e3a8a;
  color: white;
  padding: 1rem 2rem;
  font-size: 1.5rem;
  font-weight: bold;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
  border-radius: 0 0 8px 8px;
}

.logout-btn {
  background: none;
  border: none;
  font-size: 1.5rem;
  color: white;
  cursor: pointer;
}

.dashboard-container {
  max-width: 1200px;
  margin: 2rem auto;
  padding: 1rem;
}
.advisees-table {
  width: 100%;
  border-collapse: collapse;
  border-radius: 8px;
  overflow: hidden;
  box-shadow: 0 2px 8px rgba(0,0,0,0.05);
}

.advisees-table th {
  background-color: #1e3a8a;
  color: white;
  padding: 12px;
  text-align: center;
  font-weight: 600;
}

.advisees-table td {
  padding: 12px;
  border-bottom: 1px solid #ddd;
  text-align: center;
  background-color: #fff;
}

.advisees-table tbody tr:nth-child(even) td {
  background-color: #f9fafb;
}

.advisees-table tbody tr:hover td {
  background-color: #f1f5ff;
}

.advisees-table button {
  background-color: #3a86ff;
  color: white;
  border: none;
  padding: 6px 12px;
  border-radius: 4px;
  cursor: pointer;
  font-weight: 500;
  transition: background-color 0.2s ease;
}

.advisees-table button:hover {
  background-color: #265dff;
}

.atRisk td {
  background-color: #ffe6e6 !important;
}

.risk-label {
  background-color: #d9534f;
  color: white;
  padding: 4px 8px;
  border-radius: 4px;
  font-weight: bold;
}


.export-section {
  margin-top: 1.5rem;
  text-align: right;
}

.toast {
  position: fixed;
  bottom: 1.5rem;
  right: 1.5rem;
  background: #4ade80;
  color: white;
  padding: 1rem;
  border-radius: 8px;
  box-shadow: 0 0 10px rgba(0,0,0,0.2);
}


.advisees-table {
  width: 100%;
  border-collapse: collapse;
  margin-bottom: 20px;
  font-size: 1rem;
  margin-top: 1.5rem;
  border-spacing: 0;
  table-layout: auto;
}

.advisees-table th,
.advisees-table td {
  padding: 16px 20px;
  font-size: 1.1rem;       
  line-height: 1.8;        
  color: #2c3e50;
  vertical-align: top;
  text-align: center;
}

.advisees-table th {
  background-color: #f4f6f8;
}

.advisees-table tr {
  line-height: 1.6;
}

textarea {
  width: 90%;
  resize: vertical;
  font-family: Arial, sans-serif;
  padding: 5px;
}

.atRisk {
  background-color: #ffe6e6;
}

.risk-label {
  background-color: #d9534f;
  color: #fff;                     /* 字体白色 */
  padding: 4px 8px;
  border-radius: 4px;
  font-weight: bold;
}

.export-section {
  text-align: center;
  margin-top: 10px;
}

.export-btn {
  background-color: #3a86ff;
  color: white;
  border: none;
  padding: 10px 18px;
  border-radius: 5px;
  font-weight: bold;
  cursor: pointer;
}

.export-btn:hover {
  background-color: #265dff;
}

button {
  padding: 5px 10px;
  cursor: pointer;
}


.course-table td.score-cell,
.course-table th.score-cell,
.course-table td.grade{
  text-align: center;
  vertical-align: middle;
}

.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.6);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1000;
}

.modal-content {
  background: #fff;
  padding: 24px;
  border-radius: 12px;
  width: 90%;
  max-width: 800px;
  max-height: 90vh;
  overflow-y: auto;
  box-shadow: 0 6px 20px rgba(0, 0, 0, 0.3);
}

.modal-title {
  margin-bottom: 10px;
  color: #2c3e50;
}

.student-info-row {
  display: flex;
  justify-content: space-between;
  margin-bottom: 20px;
  font-size: 24px;
  font-weight: bold;
}

.section-title {
  margin-top: 20px;
  font-weight: 600;
  color: #3498db;
}

.course-card {
  background: #f9f9f9;
  padding: 16px;
  margin-top: 16px;
  border-radius: 8px;
  border-left: 5px solid #3498db;
}

.course-name {
  margin-bottom: 10px;
  color: #34495e;
}

.course-table {
  width: 100%;
  border-collapse: collapse;
}

.course-table th,
.course-table td {
  padding: 10px;
  border: 1px solid #ddd;
  text-align: left;
}

.course-table tbody tr:nth-child(even) {
  background-color: #f2f2f2;
}

.highlight-row {
  background-color: #e8f6ff;
  font-weight: bold;
}


.grade {
  padding: 4px 8px;
  border-radius: 6px;
  font-weight: bold;
  /* display: inline-block; */
  min-width: 40px;
  text-align: center;
}

.grade-a {
  background-color: #d4edda;
  color: #155724;
}

.grade-b {
  background-color: #ffeeba;
  color: #856404;
}

.grade-c {
  background-color: #fff3cd;
  color: #856404;
}

.grade-f {
  background-color: #f8d7da;
  color: #721c24;
}

.grade-none {
  background-color: #e2e3e5;
  color: #6c757d;
}


.modal-footer {
  text-align: right;
  margin-top: 20px;
}

.close-btn {
  background: #e74c3c;
  color: white;
  border: none;
  padding: 8px 14px;
  border-radius: 6px;
  cursor: pointer;
}

.close-btn:hover {
  background: #c0392b;
}

.notes-list {
  margin-top: 16px;
  margin-bottom: 20px;
}

.note-item {
  background-color: #f4f6f8;
  border-left: 4px solid #3a86ff;
  padding: 10px 12px;
  margin-bottom: 10px;
  border-radius: 6px;
}

.note-date {
  font-size: 0.85rem;
  font-weight: bold;
  color: #2c3e50;
  margin-bottom: 4px;
}

.note-text {
  font-size: 0.95rem;
  color: #333;
  white-space: pre-wrap;
}

.note-input-section {
  display: flex;
  flex-direction: column;
  gap: 10px;
  margin-top: 20px;
}

.note-input-section textarea {
  min-height: 80px;
  padding: 10px;
  font-size: 1rem;
  border: 1px solid #ccc;
  border-radius: 6px;
}

.add-note-btn {
  align-self: flex-end;
  background-color: #3a86ff;
  color: white;
  border: none;
  padding: 8px 14px;
  border-radius: 6px;
  cursor: pointer;
}

.add-note-btn:hover {
  background-color: #265dff;
}

.toast {
  position: fixed;
  bottom: 20px;
  left: 50%;
  transform: translateX(-50%);
  padding: 12px 24px;
  border-radius: 8px;
  font-weight: bold;
  color: white;
  z-index: 9999;
  box-shadow: 0 2px 8px rgba(0,0,0,0.2);
}
.toast.success {
  background-color: #28a745;
}
.toast.error {
  background-color: #dc3545;
}

</style>

