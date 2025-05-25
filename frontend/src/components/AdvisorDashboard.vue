<template>
  <div class="dashboard-container">
    <h2>Welcome, {{ advisorName }}</h2>

    <section class="advisees-section">
      <h3>Your Advisees</h3>
      <table class="advisees-table">
        <thead>
          <tr>
            <th>Matric No</th>
            <th>Name</th>
            <th>GPA</th>
            <th>Status</th>
            <th>Notes / Meeting Records</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="student in advisees" :key="student.matricNo" :class="{ atRisk: student.gpa < 2.0 }">
            <td>{{ student.matricNo }}</td>
            <td>{{ student.name }}</td>
            <td>{{ student.gpa.toFixed(2) }}</td>
            <td>
              <span v-if="student.gpa < 2.0" class="risk-label">At Risk</span>
              <span v-else>Good</span>
            </td>
            <td>
              <textarea
                v-model="student.notes"
                placeholder="Add private notes or meeting records"
                rows="2"
              ></textarea>
            </td>
            <td>
              <button @click="saveNotes(student)">Save</button>
            </td>
          </tr>
        </tbody>
      </table>
    </section>

    <section class="export-section">
      <button @click="exportReport" class="export-btn">Export Consultation Report</button>
    </section>
  </div>
</template>

<script setup>
import { ref } from 'vue';

const advisorName = ref('Dr. Johnson');

const advisees = ref([
  {
    matricNo: 'A1001',
    name: 'Alice Tan',
    gpa: 3.2,
    notes: '',
  },
  {
    matricNo: 'B2023',
    name: 'Bob Lim',
    gpa: 1.8,
    notes: 'Needs counseling, struggling with assignments.',
  },
  {
    matricNo: 'C3099',
    name: 'Charlie Lee',
    gpa: 2.5,
    notes: '',
  },
]);

function saveNotes(student) {
  alert(`Notes for ${student.name} saved:\n${student.notes}`);
}

function exportReport() {
  alert('Export consultation report: To be implemented');
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

.advisees-table {
  width: 100%;
  border-collapse: collapse;
  margin-bottom: 20px;
}

.advisees-table th,
.advisees-table td {
  border: 1px solid #ddd;
  padding: 8px;
  vertical-align: top;
  text-align: center;
}

.advisees-table th {
  background-color: #f4f6f8;
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
  color: #d9534f;
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
</style>
