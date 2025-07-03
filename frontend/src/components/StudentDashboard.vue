<template>
  <div>    
    <header class="dashboard-header">
      <div class="header-title">Student Dashboard</div>
      <button class="logout-btn" @click="handleLogout" title="Logout">🔓 Logout</button>
    </header>
   
  <div class="dashboard-container">
  <h2>Welcome, {{ studentName }}</h2>
    <!-- Course Selection Dropdown -->
    <div class="dropdown-course-container">
      <label for="course-select">Select Course: </label>
      <select id="course-select" v-model="selectedCourse" @change="onCourseChange">
        <option disabled value="">-- Choose a course --</option>
        <option v-for="course in courses" :key="course.course_id" :value="course.course_id">
          {{ course.course_code }} - {{ course.course_name }}
        </option>
      </select>
    </div>

    <!-- Tabs Navigation -->
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

    <h3 class="section-title">Your Course Marks for {{ selectedCourseName }}</h3>

    <!-- Marks Tab -->
    <section v-if="activeTab === 'marks'">
      <div v-if="grades.length" class="score-card">
        <table class="styled-table">
          <thead>
            <tr>
              <th>Component</th>
              <th>Score</th>
              <th>Max Mark</th>
              <th>Weight (%)</th>
              <th>Weighted Score</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="item in grades" :key="item.component">
              <td class="component-name">{{ item.component }}</td>
              <td>{{ item.score }}</td>
              <td>{{ item.maxMark }}</td>
              <td>{{ item.weight }}</td>
              <td>{{ weightedScore(item).toFixed(2) }}</td>
              <td>
                <GradeReviewModal
                  :courseId="courseId"
                  :scmId="item.scmId"
                />
              </td>
            </tr>
          </tbody>
        </table>
        <div class="summary">
          <div class="total-label">Total Score:</div>
          <div class="total-value">{{ totalScore.toFixed(2) }}%</div>
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
      </div>
      <div v-else class="no-data">No grades data available.</div>
    </section>

    <!-- Ranking Tab -->
    <section v-if="activeTab === 'ranking'">
      <div class="ranking-card">
        <h3 class="section-title">Your Ranking</h3>
        <div class="ranking-info">
          <div><strong>Class Rank:</strong> {{ classRank }} / {{ totalStudents }}</div>
          <div><strong>Percentile:</strong> {{ percentile.toFixed(2) }}%</div>
        </div>
        <div class="chart-container">
          <canvas id="percentileChart"></canvas>
        </div>
      </div>
    </section>

    <!-- Comparison Tab -->
    <section v-if="activeTab === 'comparison'">
      <h3 class="section-title">Compare with Coursemates (Anonymous)</h3>
      <div class="score-card">
        <table class="styled-table">
          <thead>
            <tr>
              <th>Anonymous ID</th>
              <th>Total Score</th>
              <th>Rank</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="peer in anonymousPeers"
              :key="peer.id"
              :class="{ 'highlight-row': peer.user_id === userId }"
            >
              <td class="component-name">{{ peer.id }}</td>
              <td>{{ peer.totalScore }}%</td>
              <td>{{ peer.rank }}</td>
            </tr>
          </tbody>
        </table>
        <div v-if="anonymousPeers.length === 0" class="no-data">
          No peer comparison data available.
        </div>
      </div>
    </section>

    <!-- Advisor Tab -->
<section v-if="activeTab === 'advisor'">
  <div class="advisor-card">
    <h3 class="section-title">Your Assigned Advisor</h3>

    <div v-if="advisor">
      <!-- Name and Email in one row -->
      <div class="advisor-info-row">
        <div><strong>Name:</strong> {{ advisor.advisor_name }}</div>
        <div><strong>Email:</strong> {{ advisor.advisor_email }}</div>
      </div>

      <!-- Notes Table -->
        <div class="advisor-notes-section" v-if="advisorNotes.length">
          <h4>Private Notes</h4>
          <table class="styled-table">
            <thead>
              <tr>
                <th>#</th>
                <th>Note</th>
                <th>Created At</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(note, index) in advisorNotes" :key="index">
                <td>{{ index + 1 }}</td>
                <td>{{ note.note }}</td>
                <td>{{ new Date(note.created_at).toLocaleString() }}</td>
              </tr>
            </tbody>
          </table>
        </div>

        <div v-else class="no-data">No private notes available.</div>
      </div>

      <div v-else-if="advisorError">
        <p class="error-message">{{ advisorError }}</p>
      </div>

      <div v-else>
        <p>Loading advisor info...</p>
      </div>
    </div>
  </section>
    </div>
  </div>
  
</template>

<script>
import Chart from 'chart.js/auto';
import GradeReviewModal from './GradeReviewModal.vue';

export default {
  name: 'StudentDashboard',
  components: { GradeReviewModal },
  data() {
    return {
      studentName: 'Guest',
      userId: null,
      courseId: null,
      selectedCourse: '',
      activeTab: 'marks',
      advisor: null,
      advisorError: '',
      advisorNotes: [],
      tabs: [
        { key: 'marks', label: 'Your Course Marks & Progress' },
        { key: 'ranking', label: 'Your Ranking' },
        { key: 'comparison', label: 'Compare with Coursemates (Anonymous)' },
        { key: 'advisor', label: 'Advisor Info' }, 
      ],
      courses: [],
      grades: [],
      classRank: 0,
      totalStudents: 0,
      anonymousPeers: [],
      chartInstance: null,
    };
  },
  computed: {
    totalScore() {
      return this.grades.reduce((acc, item) => acc + this.weightedScore(item), 0);
    },
    percentile() {
      if (this.totalStudents === 0) return 0;
      return ((this.totalStudents - this.classRank) / this.totalStudents) * 100;
    },
      selectedCourseName() {
    const course = this.courses.find(c => c.course_id === this.selectedCourse);
    return course ? `${course.course_code} - ${course.course_name}` : '';
  }
  },
  methods: {
    weightedScore(item) {
      if (!item.maxMark || !item.weight) return 0;
      return (item.score / item.maxMark) * item.weight;
    },
    

    // Fetch courses from backend
    async fetchCourses() {
      try {
        const res = await fetch('http://localhost:8080/student/courses', {
          method: 'GET',
          credentials: 'include',
        });
        if (!res.ok) throw new Error('Failed to fetch courses');
        this.courses = await res.json();
      } catch (err) {
        console.error(err);
        this.courses = [];
      }
    },

    // Fetch grades for selected or default course
  async fetchGrades(courseId = null) {
    // Use RESTful path parameter if courseId is provided
    let url = courseId
      ? `http://localhost:8080/student/grades/${courseId}`
      : `http://localhost:8080/student/grades`;

    try {
      const res = await fetch(url, {
        method: 'GET',
        credentials: 'include', // important for session-based auth
      });
      if (!res.ok) throw new Error('Failed to fetch grades');
      const data = await res.json();

      this.grades = data.map(item => ({
        component: item.component,
        score: Number(item.score) || 0,
        maxMark: Number(item.max_mark) || 1,
        weight: Number(item.weight) || 0,
        scmId: item.scm_id
      }));

      if (data.length > 0 && data[0].course_id) {
        this.courseId = data[0].course_id;
        this.selectedCourse = data[0].course_id;
        console.log('✅ courseId loaded from grades:', this.courseId);
      }
    } catch (err) {
      console.error(err);
      this.grades = [];
    }
  },


    // Handler when course dropdown changes
    async onCourseChange() {
      if (!this.selectedCourse) return;
      this.courseId = this.selectedCourse;
      await this.fetchGrades(this.selectedCourse);
    },

    // Fetch student's ranking data
    async fetchRanking() {
      try {
        const res = await fetch('http://localhost:8080/student/ranking', {
          credentials: 'include',
        });
        if (!res.ok) throw new Error('Failed to fetch ranking');
        const data = await res.json();
        this.classRank = data.rank || 0;
        this.totalStudents = data.total_students || 0;
        if (this.activeTab === 'ranking') this.initChart();
      } catch (err) {
        console.error(err);
      }
    },

    // Fetch anonymous peer data
    async fetchPeers() {
      try {
        const res = await fetch('http://localhost:8080/student/peers', {
          credentials: 'include',
        });
        if (!res.ok) throw new Error('Failed to fetch peers');
        this.anonymousPeers = await res.json();
      } catch (err) {
        console.error(err);
        this.anonymousPeers = [];
      }
    },

    async fetchAdvisor() {
      try {
        const res = await fetch('http://localhost:8080/student/advisor', {
          method: 'GET',
          credentials: 'include',
        });
        if (!res.ok) throw new Error('Failed to fetch advisor');

        const data = await res.json();
        this.advisor = data.length ? {
          advisor_name: data[0].advisor_name,
          advisor_email: data[0].advisor_email
        } : null;

        this.advisorNotes = data.map(entry => ({
          note: entry.note,
          created_at: entry.created_at
        }));

        console.log("Fetched advisor info:", data);
      } catch (err) {
        this.advisor = null;
        this.advisorNotes = [];
        this.advisorError = 'Unable to load advisor information.';
        console.error(err);
      }
    },

    // Load user info from session storage
    loadStudentData() {
      const userData = JSON.parse(sessionStorage.getItem('userData'));
      if (userData?.role === 'student') {
        this.studentName = userData.name || 'Student';
        this.userId = userData.id;
        this.courseId = userData.courseId;
        console.log(this.courseId);
      }
    },
    

    // Initialize or update the pie chart for ranking
    initChart() {
      this.$nextTick(() => {
        const canvas = document.getElementById('percentileChart');
        if (!canvas) return;

        const ctx = canvas.getContext('2d');
        if (this.chartInstance) this.chartInstance.destroy();

        const peopleBeat = this.totalStudents - this.classRank;
        const peopleBeatenBy = this.classRank - 1;

        this.chartInstance = new Chart(ctx, {
          type: 'pie',
          data: {
            labels: ['People You Beat', 'People Who Beat You'],
            datasets: [{
              data: [peopleBeat, peopleBeatenBy],
              backgroundColor: ['#4CAF50', '#F44336'],
              hoverOffset: 20,
            }],
          },
          options: {
            responsive: true,
            plugins: {
              legend: { position: 'bottom' },
              tooltip: {
                callbacks: {
                  label: ctx => `${ctx.label}: ${ctx.raw} people`,
                },
              },
            },
          },
        });
      });
    },
    
    handleLogout() {
      fetch('http://localhost:8080/logout', {
        method: 'POST',
        credentials: 'include'
      }).then(() => {
        window.location.href = '/'; // shared login page
      }).catch(err => {
        console.error('Logout failed:', err);
      });
    },
    
  },
  watch: {
    activeTab(newVal) {
      if (newVal === 'ranking') this.initChart();
    },
  },
  async mounted() {
  this.loadStudentData();
  await this.fetchCourses(); 
  await this.fetchAdvisor();


  // After courses are loaded, select first course if none selected
  if (!this.selectedCourse && this.courses.length > 0) {
    const firstCourse = this.courses[0];
    this.selectedCourse = firstCourse.course_id;
    this.courseId = firstCourse.course_id;
  }

  // Fetch grades for selected course
  if (this.selectedCourse) {
    await this.fetchGrades(this.selectedCourse);
  }

  this.fetchRanking();
  this.fetchPeers();
  },
};
</script>

<style scoped>

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
  max-width: 1000px;
  margin: 20px auto;
  padding: 20px;
  background: #f9fbff;
  border-radius: 12px;
  font-family: 'Segoe UI', sans-serif;
}

h2,
h3 {
  color: #2c3e50;
  margin-bottom: 15px;
}

.dropdown-course-container {
  padding: 1em;
}

.tab-nav {
  display: flex;
  gap: 10px;
  margin-bottom: 20px;
}

.tab-nav button {
  padding: 10px 20px;
  border: none;
  background-color: #dbe8ff;
  color: #3366cc;
  border-radius: 20px;
  font-weight: bold;
  transition: 0.3s;
  cursor: pointer;
}

.tab-nav button.active,
.tab-nav button:hover {
  background-color: #3366ff;
  color: #fff;
  box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
}

.section-title {
  font-size: 1.5rem;
  font-weight: 600;
  margin-bottom: 1rem;
  color: #2c3e50;
}

.score-card {
  background-color: #f9fbff;
  border: 1px solid #e0e6f2;
  border-radius: 12px;
  padding: 20px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.03);
  margin-bottom: 1.5rem;
}

.styled-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 0.95rem;
  background-color: white;
}

.styled-table th,
.styled-table td {
  padding: 10px;
  border: 1px solid #dfe4ea;
  text-align: center;
}

.styled-table thead th {
  background-color: #3a86ff;
  color: white;
  font-weight: bold;
}

.component-name {
  font-weight: 600;
  color: #2c3e50;
}

.summary {
  display: flex;
  justify-content: flex-end;
  align-items: center;
  margin-top: 1rem;
  font-size: 1.1rem;
}

.total-label {
  margin-right: 0.5rem;
  color: #34495e;
  font-weight: bold;
}

.total-value {
  color: #3a86ff;
  font-weight: 600;
}

.progress-bar {
  margin-top: 0.75rem;
  height: 25px;
  background-color: #eee;
  border-radius: 12px;
  overflow: hidden;
  width: 100%;
}

.progress-fill {
  height: 100%;
  background: linear-gradient(90deg, #4caf50 0%, #81c784 100%);
  text-align: center;
  color: white;
  font-weight: 600;
  transition: width 0.6s ease;
  line-height: 25px;
  border-radius: 12px 0 0 12px;
}

.ranking-card {
  padding: 20px;
  background: #f0f4f8;
  border-radius: 12px;
  box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
}

.ranking-info {
  display: flex;
  justify-content: space-around;
  font-size: 1.2rem;
  margin-bottom: 20px;
  color: #34495e;
}

.chart-container {
  max-width: 500px;
  margin: 0 auto;
}

.highlight-row {
  background-color: #dff0d8 !important;
  font-weight: bold;
  color: #3a6e22;
}

.no-data {
  padding: 20px;
  text-align: center;
  color: #999;
  font-style: italic;
}

  .dropdown-course-container {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  }

  .dropdown-course-container label {
    display: block;
    margin-bottom: 0.4rem;
    font-weight: 600;
    color: #333;
    font-size: 1rem;
  }

  #course-select {
    width: 100%;
    max-width: 320px;
    padding: 0.5rem 1.5rem;
    font-size: 1rem;
    border: 2px solid #4a90e2;
    border-radius: 6px;
    background-color: #fff;
    color: #333;
    appearance: none; /* Remove default arrow */
    background-image: url("data:image/svg+xml;charset=US-ASCII,%3csvg%20width%3d%2212%22%20height%3d%227%22%20viewBox%3d%220%200%2012%207%22%20xmlns%3d%22http%3a//www.w3.org/2000/svg%22%3e%3cpath%20d%3d%22M6%207L0%200h12L6%207z%22%20fill%3d%22%234a90e2%22/%3e%3c/svg%3e");
    background-repeat: no-repeat;
    background-position: right 0.8rem center;
    background-size: 12px 7px;
    cursor: pointer;
    transition: border-color 0.3s ease;
  }

  #course-select:focus {
    outline: none;
    border-color: #357ABD;
    box-shadow: 0 0 6px rgba(53, 122, 189, 0.5);
  }

  .advisor-info-row {
    display: flex;
    justify-content: space-between;
    padding: 0.5rem 0;
    font-size: 20px;
    color: #2c3e50;
    font-weight: bold;
  }

  .advisor-notes-section {
    margin-top: 1rem;
  }

</style>
