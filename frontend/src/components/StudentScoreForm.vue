<template>
  <div class="modal" v-if="visible">
    <div class="modal-content">
      <h3>Edit Scores for {{ student.name }}</h3>
      <form @submit.prevent="handleSubmit">
        <table>
          <thead>
            <tr>
              <th>Component</th>
              <th>Max Mark</th>
              <th>Score</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="comp in components" :key="comp.name">
              <td>{{ comp.name }}</td>
              <td>{{ comp.maxMark }}</td>
              <td>
                <input
                  type="number"
                  min="0"
                  :max="comp.maxMark"
                  v-model.number="localScores[comp.name]"
                  required
                />
              </td>
            </tr>
          </tbody>
        </table>

        <div class="modal-buttons">
          <button type="submit">Save</button>
          <button type="button" @click="$emit('close')">Cancel</button>
        </div>
      </form>
    </div>
  </div>
</template>

<script>
export default {
  name: 'StudentScoreForm',
  props: {
    visible: Boolean,
    student: Object,
    components: Array,
  },
  data() {
    return {
      localScores: {},
    };
  },
  watch: {
    student: {
      immediate: true,
      handler(newVal) {
        if (newVal && this.components) {
          this.localScores = {};
          this.components.forEach((comp) => {
            this.localScores[comp.name] = newVal.continuousMarks?.[comp.name] ?? 0;
          });
        }
      },
    },
    components: {
      immediate: true,
      handler() {
        if (this.student && this.components) {
          this.localScores = {};
          this.components.forEach((comp) => {
            this.localScores[comp.name] = this.student.continuousMarks?.[comp.name] ?? 0;
          });
        }
      },
    },
  },
  methods: {
    handleSubmit() {
      this.$emit('save', { scores: { ...this.localScores } });
      this.$emit('close');
    },
  },
};
</script>

<style scoped>
.modal {
  position: fixed;
  top: 0; left: 0; right: 0; bottom: 0;
  background: rgba(0, 0, 0, 0.4);
  display: flex;
  justify-content: center;
  align-items: center;
  overflow: auto;
  max-height: 100vh;
}

.modal-content {
  background: white;
  padding: 20px;
  border-radius: 8px;
  width: 400px;
  max-width: 90vw;
  max-height: 90vh;
  overflow-y: auto;
}

table {
  width: 100%;
  border-collapse: collapse;
  margin-bottom: 15px;
}

th, td {
  border: 1px solid #ddd;
  padding: 8px;
  text-align: center;
}

.modal-buttons {
  text-align: right;
}

button {
  margin-left: 10px;
  padding: 6px 14px;
  cursor: pointer;
}
</style>
