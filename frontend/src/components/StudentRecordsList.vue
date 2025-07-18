<template>
  <section>
    <table class="students-table">
      <thead>
        <tr>
          <th>Matric No</th>
          <th>Name</th>
          <th>Continuous Assessment (70%)</th>
          <th>Appeals</th>
          <th>Final Exam Score (30%)</th>
          <th>Edit Scores</th>
          <th>Total Score</th>
        </tr>
      </thead>
      <tbody>

        <tr v-for="student in filteredStudents" :key="student.matricNo">
          <td>{{ student.matricNo }}</td>
          <td>{{ student.name }}</td>
          <td>
            <div v-for="(score, comp) in getFilteredMarks(student.continuousMarks)" :key="comp"
              class="conass-container">
              <div class="conass-percent-color" :class="getScoreClass(score, getComponentMax(comp))"
                :style="{ width: getScorePercentage(score, getComponentMax(comp)) + '%' }">
                <div class="comp-name">{{ comp }}:</div>
                <div class="comp-score">{{ score }}/ {{ getComponentMax(comp) }}</div>
              </div>
            </div>

          </td>
          <td>
            <div v-for="(score, comp) in getFilteredMarks(student.continuousMarks)" :key="comp"
              class="appeal-icon-container">
              <i v-if="hasAppeal(student, comp) && getAppealStatus(student, comp) === 'pending'"
                class="bi bi-envelope-exclamation-fill appeal-icon" title="View Appeal"
                @click="openAppealModal(student, comp)"></i>
            </div>
          </td>
          <td>{{ student.finalExam }}</td>
          <td><button class="edit-btn" @click="$emit('edit-student', student)">Edit</button></td>
          <td>{{ calculateTotalScore(student).toFixed(2) }}%</td>
        </tr>
      </tbody>
    </table>
    <AppealReviewModal v-if="showModal" :student="selectedStudent" :component="selectedComponent"
      :appeal="selectedAppeal" @close="closeModal" @respond="handleResponse" />
  </section>
</template>

<script>
import AppealReviewModal from './AppealReviewModal.vue';
export default {
  name: "StudentRecordsList",
  data() {
    return {
      showModal: false,
      selectedStudent: null,
      selectedComponent: null,
      selectedAppeal: null,
    };
  },
  components: { AppealReviewModal },
  props: {
    students: Array,
    components: Array,
    finalExamMax: {
      type: Number,
      default: 100,
    },
    searchQuery: String,
    appeals: {
      type: Object,
      default: () => ({}),
    },
  },
  computed: {
    filteredStudents() {
      if (!this.searchQuery) return this.students;
      const query = this.searchQuery.toLowerCase();
      return this.students.filter(student =>
        student.name.toLowerCase().includes(query)
      );
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
    getScmId(matricNo, componentName) {
      // Match how scm_id is stored/generated in your DB (adjust if needed)
      return `${matricNo}_${componentName}`;
    },
    hasAppeal(student, component) {
      const scmId = this.getScmId(student.matricNo, component);
      return !!this.appeals[scmId];
    },
    getAppealStatus(student, component) {
      const scmId = this.getScmId(student.matricNo, component);
      return this.appeals[scmId]?.status || '';
    },
    getAppealReason(student, component) {
      const scmId = this.getScmId(student.matricNo, component);
      return this.appeals[scmId]?.reason || '';
    },
    openAppealModal(student, component) {
      const scmId = this.getScmId(student.matricNo, component);
      this.selectedStudent = student;
      this.selectedComponent = component;
      this.selectedAppeal = this.appeals[scmId];
      this.showModal = true;
    },
    closeModal() {
      this.showModal = false;
      this.selectedStudent = null;
      this.selectedComponent = null;
      this.selectedAppeal = null;
    },
    handleResponse(status) {
      this.$emit('respond-appeal', this.selectedStudent, this.selectedComponent, status);
      this.closeModal();
    },
  },
};
</script>