<template>
  <section>
    <table class="components-table">
      <thead>
        <tr>
          <th>Component</th>
          <th>Max Mark</th>
          <th>Weight (%)</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="comp in components" :key="comp.name">
          <td>{{ comp.name }}</td>
          <td>{{ comp.maxMark }}</td>
          <td>{{ comp.weight }}%</td>
          <td>
            <button class="edit-btn" @click="$emit('edit-component', comp)">Edit</button>
            <button class="delete-btn" @click="$emit('delete-component', comp.name)">Delete</button>
          </td>
        </tr>
      </tbody>
    </table>

    <div class="add-btn-wrapper">
      <button class="add-btn" @click="$emit('add-component')">+ Add Component</button>
    </div>

    <div v-if="showComponentModal" class="modal-overlay">
      <div class="modal">
        <h3>{{ isEditingComponent ? 'Edit' : 'Add' }} Component</h3>
        <label>Component Name:</label>
        <input v-model="localForm.name" :readonly="isEditingComponent" />
        <label>Max Mark:</label>
        <input type="number" v-model.number="localForm.maxMark" min="0" />
        <label>Weight (%):</label>
        <input type="number" v-model.number="localForm.weight" min="0" max="70" />
        <div class="modal-buttons">
          <button class="save-btn" @click="save">Save</button>
          <button class="cancel-btn" @click="$emit('cancel-component-modal')">Cancel</button>
        </div>
      </div>
    </div>
  </section>
</template>

<script>
export default {
  name: "ContinuousAssessmentComponents",
  props: {
    components: Array,
    showComponentModal: Boolean,
    isEditingComponent: Boolean,
    componentForm: Object,
  },
  data() {
    return {
      localForm: { name: "", maxMark: 0, weight: 0 },
    };
  },
  watch: {
    componentForm: {
      immediate: true,
      handler(newVal) {
        this.localForm = { ...newVal };
      },
    },
  },
  methods: {
    save() {
      this.$emit("save-component", { ...this.localForm });
    },
  },
};
</script>
