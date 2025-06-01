<template>
  <div class="dashboard-container">
    <h2>Welcome, {{ studentName }}</h2>

    <!-- Tab导航 -->
    <nav class="tab-nav">
      <button
        v-for="tab in tabs"
        :key="tab.key"
        :class="{ active: activeTab === tab.key }"
        @click="activeTab = tab.key"
      >
        {{ tab.label }}
      </button>
    </nav>

    <!-- Tab内容切换 -->
    <section v-if="activeTab === 'marks'">
      <h3>Your Course Marks & Progress</h3>
      <table class="grades-table">
        <thead>
          <tr>
            <th>Component</th>
            <th>Score</th>
            <th>Max Mark</th>
            <th>Weight (%)</th>
            <th>Weighted Score</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="item in grades" :key="item.component">
            <td>{{ item.component }}</td>
            <td>{{ item.score }}</td>
            <td>{{ item.maxMark }}</td>
            <td>{{ item.weight }}</td>
            <td>{{ (item.score / item.maxMark * item.weight).toFixed(2) }}</td>
          </tr>
        </tbody>
      </table>
      <div class="total-score">
        <strong>Total Score: {{ totalScore.toFixed(2) }}%</strong>
      </div>
      <div class="progress-bar">
        <div
          class="progress-fill"
          :style="{ width: totalScore + '%' }"
          :aria-valuenow="totalScore"
          aria-valuemin="0"
          aria-valuemax="100"
        >
          {{ totalScore.toFixed(1) }}%
        </div>
      </div>
    </section>

    <section v-if="activeTab === 'ranking'">
      <h3>Your Ranking</h3>
      <p>Class Rank: {{ classRank }} / {{ totalStudents }}</p>
      <p>Percentile: {{ percentile.toFixed(2) }}%</p>
    </section>

    <section v-if="activeTab === 'comparison'">
      <h3>Compare with Coursemates (Anonymous)</h3>
      <table class="comparison-table">
        <thead>
          <tr>
            <th>Anonymous ID</th>
            <th>Total Score</th>
            <th>Rank</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="peer in anonymousPeers" :key="peer.id">
            <td>{{ peer.id }}</td>
            <td>{{ peer.totalScore }}%</td>
            <td>{{ peer.rank }}</td>
          </tr>
        </tbody>
      </table>
    </section>
  </div>
</template>

<script>
export default {
  name: 'StudentDashboard',
  data() {
    return {
      studentName: 'Guest',
      activeTab: 'marks',
      tabs: [
        { key: 'marks', label: 'Your Course Marks & Progress' },
        { key: 'ranking', label: 'Your Ranking' },
        { key: 'comparison', label: 'Compare with Coursemates (Anonymous)' },
      ],
      grades: [
        { component: 'Quiz 1', score: 18, maxMark: 20, weight: 10 },
        { component: 'Assignment 1', score: 45, maxMark: 50, weight: 20 },
        { component: 'Lab Exercises', score: 25, maxMark: 30, weight: 15 },
        { component: 'Test 1', score: 38, maxMark: 40, weight: 25 },
        { component: 'Final Exam', score: 70, maxMark: 100, weight: 30 },
      ],
      classRank: 5,
      totalStudents: 100,
      anonymousPeers: [
        { id: 'Anon001', totalScore: 85, rank: 3 },
        { id: 'Anon002', totalScore: 78, rank: 7 },
        { id: 'Anon003', totalScore: 92, rank: 1 },
        { id: 'Anon004', totalScore: 68, rank: 15 },
      ],
    };
  },
  methods: {
    loadStudentData() {
      const userData = JSON.parse(sessionStorage.getItem('userData'));
      if (userData?.role === 'student') {
        this.studentName = userData.name || 'Student';
      }
    }
  },
  computed: {
    totalScore() {
      return this.grades.reduce(
        (acc, item) => acc + (item.score / item.maxMark) * item.weight,
        0
      );
    },
    percentile() {
      return ((this.totalStudents - this.classRank) / this.totalStudents) * 100;
    },
  },
  mounted() {
    this.loadStudentData();
  },
};
</script>

<style scoped>
.dashboard-container {
  max-width: 900px;
  margin: 20px auto;
  padding: 20px;
  background: white;
  border-radius: 8px;
  box-shadow: 0 0 10px #ccc;
  font-family: Arial, sans-serif;
}

h2,
h3 {
  color: #2c3e50;
  margin-bottom: 15px;
}

.tab-nav {
  display: flex;
  gap: 10px;
  margin-bottom: 20px;
}

.tab-nav button {
  padding: 8px 16px;
  border: none;
  background-color: #e1e1e1;
  cursor: pointer;
  border-radius: 5px;
  font-weight: bold;
  transition: background-color 0.3s;
}

.tab-nav button.active,
.tab-nav button:hover {
  background-color: #3a86ff;
  color: white;
}

table {
  width: 100%;
  border-collapse: collapse;
  margin-bottom: 15px;
}

th,
td {
  border: 1px solid #ddd;
  padding: 8px;
  text-align: center;
}

th {
  background-color: #f4f6f8;
}

.total-score {
  text-align: right;
  font-size: 1.2em;
  margin-bottom: 20px;
  color: #34495e;
}

.progress-bar {
  background-color: #eee;
  border-radius: 20px;
  overflow: hidden;
  height: 25px;
  width: 100%;
  max-width: 400px;
}

.progress-fill {
  height: 100%;
  background-color: #3a86ff;
  color: white;
  font-weight: bold;
  line-height: 25px;
  text-align: center;
  transition: width 0.5s ease-in-out;
}

p {
  font-size: 1.1em;
  margin: 8px 0;
}

@media (max-width: 600px) {
  table {
    font-size: 0.9em;
  }

  .dashboard-container {
    padding: 10px;
  }
}
</style>