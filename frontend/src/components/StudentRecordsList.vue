<template>
  <section>
    <h3>Student Records & Total Scores</h3>
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
        <tr v-for="student in students" :key="student.matricNo">
          <td>{{ student.matricNo }}</td>
          <td>{{ student.name }}</td>
          <td>
            <ul>
              <li
                v-for="(score, comp) in getFilteredMarks(student.continuousMarks)"
                :key="comp"
              >
                <span class="comp-name">{{ comp }}:</span>
                <span class="comp-score">{{ score }}</span>
              </li>
            </ul>
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
    calculateTotalScore(student) {
      const continuousScore = this.components.reduce((acc, comp) => {
        const score = student.continuousMarks?.[comp.name] || 0;
        return acc + (score / comp.maxMark) * comp.weight;
      }, 0);
      const finalScore = (student.finalExam / this.finalExamMax) * 30;
      return continuousScore + finalScore;
    },
  },
};
</script>
