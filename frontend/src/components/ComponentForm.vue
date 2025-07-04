<template>
  <div class="modal" v-if="visible">
    <div class="modal-content">
      <h3>{{ isEdit ? 'Edit' : 'Add New' }} Component</h3>
      <form @submit.prevent="handleSubmit">
        <label>Component Name:</label>
        <input type="text" v-model="form.name" :readonly="isEdit" required />

        <label>Max Mark:</label>
        <input type="number" min="1" v-model.number="form.maxMark" required />

        <label>Weight (%):</label>
        <input type="number" min="0" max="100" v-model.number="form.weight" required />

        <div class="modal-buttons">
          <button type="submit">{{ isEdit ? 'Save' : 'Add' }}</button>
          <button type="button" @click="$emit('close')">Cancel</button>
        </div>
      </form>
    </div>
  </div>
</template>

<script>
export default {
  name: 'ComponentForm',
  props: {
    visible: Boolean,
    componentData: Object,
  },
  data() {
    return {
      form: {
        name: '',
        maxMark: 0,
        weight: 0,
      },
    };
  },
  computed: {
    isEdit() {
      return !!this.componentData;
    },
    projectedTotalWeight() {
      return null;
    }
  },
  watch: {
    componentData: {
      immediate: true,
      handler(newVal) {
        if (newVal) {
          this.form.name = newVal.name;
          this.form.maxMark = newVal.maxMark;
          this.form.weight = newVal.weight;
        } else {
          this.form.name = '';
          this.form.maxMark = 0;
          this.form.weight = 0;
        }
      },
    },
  },
  methods: {
    handleSubmit() {
      this.$emit('save', { ...this.form });
      this.$emit('close');
    },
  },
};
</script>

<style scoped>
.modal {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
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
  width: 320px;
  max-width: 90vw;
}

label {
  display: block;
  margin-top: 10px;
}

input {
  width: 100%;
  padding: 6px;
  margin-top: 4px;
  box-sizing: border-box;
}

.modal-buttons {
  margin-top: 15px;
  text-align: right;
}

button {
  margin-left: 10px;
  padding: 6px 14px;
  cursor: pointer;
}
</style>
