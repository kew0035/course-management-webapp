<template>
  
  <section class="statistics-section">
    <div class="stats-summary">
      <p>Total Students: <strong>{{ totalStudents }}</strong></p>
      <p>Average Total Score: <strong>{{ averageTotalScore.toFixed(2) }}%</strong></p>
      <p>Highest Score: <strong>{{ highestScore.toFixed(2) }}%</strong></p>
      <p>Lowest Score: <strong>{{ lowestScore.toFixed(2) }}%</strong></p>
    </div>

    <ScorePieChart
      v-if="hasScores && distribution && Object.keys(distribution).length > 0"
      :distribution="distribution"
    />

    <div>
    <h2>📋 学生分数测试</h2>
    <p>👥 学生总数: {{ totalStudents }}</p>
    <ul>
      <li v-for="(score, index) in scores" :key="index">
        学生 {{ index + 1 }} 的总分: {{ score }}
      </li>
    </ul>
  </div>


  </section>
</template>

<script>
import ScorePieChart from "./ScorePieChart.vue";

export default {
  name: "StatisticsView",
  components: { ScorePieChart },
  props: {
    students: {
      type: Array,
      default: () => [],
    },
  },
  computed: {
    totalStudents() {
      return this.students.length;
    },
    scores() {
      return this.students.map((s) => s.totalScore || 0);
    },
    averageTotalScore() {
      if (this.scores.length === 0) return 0;
      const sum = this.scores.reduce((a, b) => a + b, 0);
      return sum / this.scores.length;
    },
    highestScore() {
      if (this.scores.length === 0) return 0;
      return Math.max(...this.scores);
    },
    lowestScore() {
      if (this.scores.length === 0) return 0;
      return Math.min(...this.scores);
    },
    distribution() {
      const buckets = {};
      for (let i = 0; i < 100; i += 10) {
        buckets[`${i}-${i + 10}%`] = 0;
      }
      this.scores.forEach((score) => {
        const bucket = Math.min(Math.floor(score / 10) * 10, 90);
        const key = `${bucket}-${bucket + 10}%`;
        buckets[key]++;
      });
      return buckets;
    },
    hasScores() {
      return this.scores.length > 0;
    },
  },
};
</script>

<style scoped>
.statistics-section {
  max-width: 500px;
  margin: 0 auto;
  font-weight: 600;
  color: #34495e;
}

.stats-summary p {
  font-size: 1.1rem;
  margin-bottom: 8px;
  user-select: none;
}
</style>
