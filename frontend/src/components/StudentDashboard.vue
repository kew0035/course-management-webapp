<template>
  <div class="dashboard-container">
    <h2>Welcome, {{ studentName }}</h2>

    <!-- Tab-->
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

    <!-- Results -->
    <section v-if="activeTab === 'marks'">
      <h3 class="section-title">Your Course Marks & Progress</h3>
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
                :componentName="item.component"
                :studId="userId"
                :courseId="courseId"
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

    <!-- Ranking -->
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

    <!-- Comparison -->
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
        <div v-if="anonymousPeers.length === 0" class="no-data">No peer comparison data available.</div>
      </div>
    </section>
  </div>
</template>

<script>
import Chart from 'chart.js/auto';
import GradeReviewModal from './GradeReviewModal.vue';

export default {
  name: 'StudentDashboard',
  components: {
    GradeReviewModal
  },
  data() {
    return {
      studentName: 'Guest',
      userId: null,
      courseId: null,
      activeTab: 'marks',
      tabs: [
        { key: 'marks', label: 'Your Course Marks & Progress' },
        { key: 'ranking', label: 'Your Ranking' },
        { key: 'comparison', label: 'Compare with Coursemates (Anonymous)' },
      ],
      grades: [],
      classRank: 0,
      totalStudents: 0,
      anonymousPeers: [],
      chartInstance: null,
    };
  },
  methods: {
    weightedScore(item) {
      if (!item.maxMark || !item.weight) return 0;
      return (item.score / item.maxMark) * item.weight;
    },
    requestReview(item) {
      alert(`Review requested for ${item.component}`);
      // 可进一步调用API或打开modal
    },
    async fetchGrades() {
      try {
        const res = await fetch('http://localhost:8080/student/grades', {
          method: 'GET',
          credentials: 'include',
        });
        if (!res.ok) throw new Error('Failed to fetch grades');
        const data = await res.json();
        this.grades = data.map(item => ({
          component: item.component,
          score: Number(item.score) || 0,
          maxMark: Number(item.max_mark) || 1,
          weight: Number(item.weight) || 0,
        }));
        if (data.length > 0 && data[0].course_id) {
          this.courseId = data[0].course_id;
          console.log("✅ courseId loaded from grades:", this.courseId);
        }
      } catch (err) {
        console.error(err);
      }
    },
    async fetchRanking() {
      try {
        const res = await fetch('http://localhost:8080/student/ranking', {
          credentials: 'include',
        });
        const data = await res.json();
        this.classRank = data.rank || 0;
        this.totalStudents = data.total_students || 0;
        if (this.activeTab === 'ranking') {
          this.initChart();
        }
      } catch (err) {
        console.error(err);
      }
    },
    async fetchPeers() {
      try {
        const res = await fetch('http://localhost:8080/student/peers', {
          credentials: 'include',
        });
        const data = await res.json();
        console.log(data);
        this.anonymousPeers = data;
      } catch (err) {
        console.error(err);
      }
    },
    loadStudentData() {
      const userData = JSON.parse(sessionStorage.getItem('userData'));
      if (userData?.role === 'student') {
        this.studentName = userData.name || 'Student';
        this.userId = userData.id;
        this.courseId = userData.courseId;
      }
    },
    initChart() {
      this.$nextTick(() => {
        const canvas = document.getElementById('percentileChart');
        if (!canvas) return;
        const ctx = canvas.getContext('2d');
        if (this.chartInstance) {
          this.chartInstance.destroy();
        }

        // Calculate the number of people who surpass you and the number of people who are surpassed (classRank excludes yourself)
        const peopleBeat = this.totalStudents - this.classRank; 
        const peopleBeatenBy = this.classRank -1; 

        this.chartInstance = new Chart(ctx, {
          type: 'pie',
          data: {
            labels: ['People You Beat', 'People Who Beat You'],
            datasets: [{
              data: [peopleBeat, peopleBeatenBy],
              backgroundColor: ['#4CAF50', '#F44336'], // green/red
              hoverOffset: 20,
            }],
          },
          options: {
            responsive: true,
            plugins: {
              legend: {
                position: 'bottom',
                maintainAspectRatio: false,
                labels: {
                  font: {
                    size: 14,
                  },
                },
              },
              tooltip: {
                callbacks: {
                  label: ctx => {
                    const label = ctx.label || '';
                    const value = ctx.raw || 0;
                    return `${label}: ${value} people`;
                  },
                },
              },
            },
          },
        });
      });
    },
  },
  watch: {
    activeTab(newVal) {
      if (newVal === 'ranking') {
        this.initChart();
      }
    },
  },
  computed: {
    totalScore() {
      return this.grades.reduce((acc, item) => acc + this.weightedScore(item), 0);
    },
    percentile() {
      if (this.totalStudents === 0) return 0;
      return ((this.totalStudents - this.classRank) / this.totalStudents) * 100;
    },
  },

  mounted() {
    this.loadStudentData();
    this.fetchGrades();
    this.fetchRanking();
    this.fetchPeers();
  },
};
</script>

<style scoped>
.dashboard-container {
  max-width: 1000px;
  margin: 20px auto;
  padding: 20px;
  background: #f9fbff;
  border-radius: 12px;
  font-family: 'Segoe UI', sans-serif;
}

h2, h3 {
  color: #2c3e50;
  margin-bottom: 15px;
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

.styled-table th, .styled-table td {
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
  border-radius: 30px;
  overflow: hidden;
}

.progress-fill {
  height: 100%;
  background: linear-gradient(90deg, #4a90e2, #3578e5);
  color: white;
  text-align: center;
  line-height: 25px;
  font-weight: 600;
  transition: width 0.4s ease;
}

.no-data {
  padding: 15px;
  color: #999;
  text-align: center;
  font-style: italic;
}

.ranking-info {
  font-size: 1rem;
  color: #2c3e50;
  display: flex;
  justify-content: space-between;
  padding: 10px 0;
}

.highlight-row {
  background-color: #d1f0ff !important;
  font-weight: bold;
  color: #0a588d;
}

.ranking-card {
  padding: 1rem;
  background: #f8fbff;
  border-radius: 12px;
  box-shadow: 0 0 6px rgba(0, 0, 0, 0.05);
}

.ranking-details {
  display: flex;
  justify-content: space-between;
  margin-bottom: 1rem;
  font-size: 1.1rem;
}

.chart-container {
  width: 300px;  
  height: 300px;
  margin: 0 auto;
}

/* .review-btn {
  background-color: #3a86ff;
  border: none;
  padding: 6px 12px;
  color: white;
  font-weight: bold;
  border-radius: 5px;
  cursor: pointer;
}

.review-btn:hover {
  background-color: #2c3e50;
} */

</style>
