<template>
  <section>
    <table class="students-table">
      <thead>
        <tr>
          <th>Matric No</th>
          <th>Name</th>
          <th>Continuous Assessment (70%)</th>
          <th>Final Exam Score (30%)</th>
          <th>Edit Scores</th>
          <th>Total Score</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="student in students" :key="student.matricNo" >
          <td>{{ student.matricNo }}</td>
          <td>{{ student.name }}</td>
          <td>
            <div v-for="(score, comp) in getFilteredMarks(student.continuousMarks)" :key="comp"  class="conass-container">
              <div class="conass-percent-color" :class="getScoreClass(score, getComponentMax(comp))"
              :style="{ width: getScorePercentage(score, getComponentMax(comp)) + '%' }">
                <div class="comp-name">{{ comp }}:</div>  
                <div class="comp-score">{{ score }}/ {{ getComponentMax(comp) }}</div>
              </div>
            </div>

<!-- 
            <ul>
              <li
                v-for="(score, comp) in getFilteredMarks(student.continuousMarks)"
                :key="comp"
                :class="getScoreClass(score, getComponentMax(comp))"
              >
                <span class="comp-name">{{ comp }}:</span>
                <span class="comp-score" >{{ score }}/ {{ getComponentMax(comp) }}</span>
              </li>
            </ul> -->
          </td>
          <td>{{ student.finalExam }}</td>
          <td><button class="edit-btn" @click="$emit('edit-student', student)">Edit</button></td>
          <td>{{ calculateTotalScore(student).toFixed(2) }}%</td>
        </tr>
      </tbody>
    </table>
  </section>
</template>

<script>
export default {
  name: "StudentRecordsList",
  props: {
    students: Array,
    components: Array,
    finalExamMax: {
      type: Number,
      default: 100,
    },
  },
  methods: {
    getFilteredMarks(marks) {
      return Object.entries(marks)
        .filter(([key]) => key !== "final_exam")
        .reduce((obj, [k, v]) => ({ ...obj, [k]: v }), {});
    },
    getComponentMax(compName) {
      const comp = this.components.find(c => c.name === compName);
      return comp ? comp.maxMark : 0;
    },
    calculateTotalScore(student) {
      const continuousScore = this.components.reduce((acc, comp) => {
        const score = student.continuousMarks?.[comp.name] || 0;
        return acc + (score / comp.maxMark) * comp.weight;
      }, 0);
      const finalScore = (student.finalExam / this.finalExamMax) * 30;
      return continuousScore + finalScore;
    },
    getScorePercentage(score, max) {
      if (!max) return 0;
      return (score / max) * 100;
    },
    getScoreClass(score, max) {
      const percentage = (score / max) * 100;
      if (percentage >= 80) return "high-score";
      if (percentage >= 50) return "mid-score";
      return "low-score";
    },
  },
};
</script>

<style>
.high-score, .mid-score, .low-score{
  font-weight: bold;
  padding: 4px;
  border-radius: 4px;
}

.high-score {
  background-color: rgb(10, 198, 10); /* light green */
}

.mid-score {
  background-color: rgb(255, 213, 0); /* light orange */
}

.low-score {
  background-color: red; /* light red */
}

.conass-percent-color{
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.5em 0.8em;
  border-radius: 1em;
  white-space: nowrap;
  gap: 1em;
  box-sizing: border-box;
}
</style>