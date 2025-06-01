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
      <table class="grades-table" v-if="grades.length">
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
            <td>{{ weightedScore(item).toFixed(2) }}</td>
          </tr>
          
        </tbody>
      </table>
      <div v-else>No grades data available.</div>
      <div class="total-score" v-if="grades.length">
        <strong>Total Score: {{ totalScore.toFixed(2) }}%</strong>
      </div>
      <div class="progress-bar" v-if="grades.length">
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
      <table class="comparison-table" v-if="anonymousPeers.length">
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
      <div v-else>No peer comparison data available.</div>
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
      grades: [], // 从后端动态拉取的成绩数据
      classRank: 0,
      totalStudents: 0,
      anonymousPeers: [],
    };
  },
  methods: {
    weightedScore(item) {
      if (!item.maxMark || !item.weight) return 0;
      return (item.score / item.maxMark) * item.weight;
    },
    async fetchGrades() {
      try {
        const res = await fetch('http://localhost:8080/student/grades', {
          method: 'GET',
          credentials: 'include',
        });
        if (!res.ok) throw new Error('Failed to fetch grades');
        const data = await res.json();

        // 这里假设后端返回格式：
        // [{component, score, max_mark, weight, ...}, ...]
        // 转换字段名称，兼容前端使用
        this.grades = data.map(item => ({
          component: item.component,
          score: Number(item.score) || 0,
          maxMark: Number(item.max_mark) || 1,
          weight: Number(item.weight) || 0,
        }));
      } catch (error) {
        console.error(error);
      }
    },
    // async fetchRanking() {
    //   try {
    //     const res = await fetch('http://localhost:8080/student/ranking', {
    //       credentials: 'include',
    //     });
    //     if (!res.ok) throw new Error('Failed to fetch ranking');
    //     const data = await res.json();

    //     this.classRank = data.rank || 0;
    //     this.totalStudents = data.total_students || 0;
    //   } catch (error) {
    //     console.error(error);
    //   }
    // },
    // async fetchPeers() {
    //   try {
    //     const res = await fetch('http://localhost:8080/student/peers', {
    //       credentials: 'include',
    //     });
    //     if (!res.ok) throw new Error('Failed to fetch peer data');
    //     const data = await res.json();

    //     this.anonymousPeers = data || [];
    //   } catch (error) {
    //     console.error(error);
    //   }
    // },
    loadStudentData() {
      const userData = JSON.parse(sessionStorage.getItem('userData'));
      if (userData?.role === 'student') {
        this.studentName = userData.name || 'Student';
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
    // this.fetchRanking();
    // this.fetchPeers();
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
