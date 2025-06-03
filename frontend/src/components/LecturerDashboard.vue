<template>
  <div class="dashboard-container">
    <h2>Welcome, {{ lecturerName }}</h2>

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

    <StudentRecordsList
      v-if="activeTab === 'students'"
      :students="students"
      :components="components"
      :finalExamMax="finalExamMax"
      @edit-student="openEditScoreModal"
    />

    <ContinuousAssessmentComponents
      v-if="activeTab === 'components'"
      :components="components"
      :showComponentModal="showComponentModal"
      :isEditingComponent="isEditingComponent"
      :componentForm="componentForm"
      @edit-component="openEditComponentModal"
      @delete-component="promptDeleteComponent"
      @add-component="openAddComponentModal"
      @save-component="saveComponent"
      @cancel-component-modal="showComponentModal = false"
    />

    <StatisticsView
      v-if="activeTab === 'statistics'"
      :students="students"
      :averageTotalScore="students.length ? students.reduce((sum, s) => sum + s.totalScore, 0) / students.length : 0"
    />

    <!-- Edit student grades pop-up window -->
    <div v-if="showEditModal" class="modal-overlay">
      <div class="modal">
        <h3>Edit Scores for {{ editingStudent.name }}</h3>
        <div v-for="comp in filteredComponents" :key="comp.name" class="score-input-group">
          <label>{{ comp.name }}:</label>
          <input
            type="number"
            min="0"
            :max="comp.maxMark"
            v-model.number="editingScores[comp.name]"
            @input="validateScore(comp.name, comp.maxMark)"
          />
          <small class="max-mark">Max: {{ comp.maxMark }}</small>
        </div>
        <div class="score-input-group">
          <label>Final Exam Score:</label>
          <input type="number" min="0" max="100" v-model.number="editingScores.final_exam" />
          <small class="max-mark">Max: 100</small>
        </div>
        <div class="modal-buttons">
          <button class="save-btn" @click="saveEditedScores">Save</button>
          <button class="cancel-btn" @click="showEditModal = false">Cancel</button>
        </div>
      </div>
    </div>
    <div v-if="showToast" class="custom-toast">
      {{ toastMessage }}
    </div>
    <div v-if="showToast" :class="['custom-toast', toastType]">
      {{ toastMessage }}
    </div>
    <div v-if="showDeleteConfirm" class="modal-overlay">
      <div class="modal">
        <h3>Confirm Deletion</h3>
        <p>Are you sure you want to delete the component: <strong>{{ pendingDeleteComponent }}</strong>?</p>
        <div class="modal-buttons">
          <button class="delete-btn" @click="confirmDelete">Yes, Delete</button>
          <button class="cancel-btn" @click="showDeleteConfirm = false">Cancel</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import StudentRecordsList from "./StudentRecordsList.vue";
import ContinuousAssessmentComponents from "./ContinuousAssessmentComponents.vue";
import StatisticsView from "./Statistics.vue";

export default {
  name: "LecturerDashboard",
  props: {
    name: {
      type: String,
      default: "Guest"
    }
  },
  components: {
    StudentRecordsList,
    ContinuousAssessmentComponents,
    StatisticsView,
  },
  data() {
    return {
      lecturerName: 'Guest',
      finalExamMax: 100,
      tabs: [
        { key: "students", label: "Student Records & Total Scores" },
        { key: "components", label: "Continuous Assessment Components (70%)" },
        { key: "statistics", label: "Statistics" },
      ],
      activeTab: "students",
      students: [],
      components: [],
      showEditModal: false,
      editingStudent: null,
      editingScores: {},

      showComponentModal: false,
      isEditingComponent: false,
      componentForm: {
        name: "",
        maxMark: 0,
        weight: 0,
      },
      toastMessage: "",
      showToast: false,
      toastType: "success",
      pendingDeleteComponent: null,
      showDeleteConfirm: false,
    };
    
  },

  computed: {
    filteredComponents() {
      return this.components.filter(
        (comp) => comp.name !== "final_exam" && comp.name !== "final"
      );
    },
    averageTotalScore() {
      if (this.students.length === 0) return 0;
      const sum = this.students.reduce(
        (acc, stu) => acc + this.calculateTotalScore(stu),
        0
      );
      return sum / this.students.length;
    },
  },
  methods: {
    validateScore(compName, maxMark) {
      if (this.editingScores[compName] > maxMark) {
        this.editingScores[compName] = maxMark;
      } else if (this.editingScores[compName] < 0) {
        this.editingScores[compName] = 0;
      }
    },
    validateFinalExamScore() {
      if (this.editingScores.final_exam > 100) this.editingScores.final_exam = 100;
      if (this.editingScores.final_exam < 0) this.editingScores.final_exam = 0;
    },

    calculateTotalScore(student) {
      let totalWeight = 0;
      let weightedScoreSum = 0;

      for (const comp of this.components) {
        const score = parseFloat(student.continuousMarks[comp.name] ?? 0);
        const max = parseFloat(comp.maxMark ?? 1);
        const weight = parseFloat(comp.weight ?? 0);

        if (isNaN(score) || isNaN(max) || max <= 0) continue;

        weightedScoreSum += (score / max) * weight;
        totalWeight += weight;
      }

      const finalWeight = 100 - totalWeight;
      const finalExamScore = parseFloat(student.finalExam ?? 0);

      const finalComponent = (finalExamScore / 100) * finalWeight;

      return parseFloat((weightedScoreSum + finalComponent).toFixed(2));
    },


    async fetchComponents() {
      const res = await fetch("http://localhost:8080/lecturer/components");
      const data = await res.json();

      this.components = data.map((comp) => ({
        name: comp.component,
        maxMark: comp.max_mark,
        weight: comp.weight,
      }));
    },

    async fetchStudents() {
      const res = await fetch("http://localhost:8080/lecturer/students");
      const data = await res.json();
      const comps = this.components;
      console.log("📦 Raw student data:", data[0]);

      this.students = data.map((stu) => {
        let continuousMarks;

        // 处理 continuous_marks 字段是否是字符串（可能是 JSON 字符串）
        if (typeof stu.continuous_marks === "string") {
          try {
            continuousMarks = JSON.parse(stu.continuous_marks);
          } catch (e) {
            console.error("❌ JSON parse error:", e);
            continuousMarks = {};
          }
        } else {
          continuousMarks = stu.continuous_marks || {};
        }

        // 给缺少的 component 分数补 0
        comps.forEach((comp) => {
          if (!(comp.name in continuousMarks)) {
            continuousMarks[comp.name] = 0;
          }
        });

        const student = {
          matricNo: stu.matric_no,
          name: stu.name,
          continuousMarks,
          finalExam: parseFloat(stu.final_exam_score ?? 0),
        };
        student.totalScore = this.calculateTotalScore(student);
        return student;
      });
    },

    saveEditedScores() {
      this.filteredComponents.forEach((comp) =>
        this.validateScore(comp.name, comp.maxMark)
      );
      this.validateFinalExamScore();
      const { final_exam, ...continuousMarks } = this.editingScores;

      const updatedScores = {
        matric_no: this.editingStudent.matricNo,
        continuous_marks: JSON.stringify(continuousMarks),
        final_exam: final_exam,
      };
      fetch("http://localhost:8080/lecturer/update-scores", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(updatedScores),
      })
        .then((res) => {
          if (!res.ok) throw new Error("Failed to update scores");
          return res.json();
        }).then(() => {
          this.editingStudent.continuousMarks = { ...this.editingScores };
          this.editingStudent.finalExam = this.editingScores.final_exam;
          this.showEditModal = false;

          this.showSuccessToast(`✅ Scores for ${this.editingStudent.name} updated successfully.`);

          this.showToast = true;
          setTimeout(() => this.showToast = false, 3000);
        })
        .catch((err) => {
          console.error(err);
          this.showErrorToast("❌ Failed to update scores.");
        });
    },
    openEditScoreModal(student) {
      this.editingStudent = student;
      this.editingScores = { ...student.continuousMarks, final_exam: student.finalExam };
      this.showEditModal = true;
    },
    openAddComponentModal() {
      this.isEditingComponent = false;
      this.componentForm = { name: "", maxMark: 0, weight: 0 };
      this.showComponentModal = true;
    },
    openEditComponentModal(comp) {
      this.isEditingComponent = true;
      this.componentForm = { ...comp };
      this.showComponentModal = true;
    },

    async saveComponent(componentToSave) {
      const payload = {
        name: componentToSave.name.trim(),
        maxMark: parseInt(componentToSave.maxMark),
        weight: parseInt(componentToSave.weight),
      };

      console.log("🚀 Sending component data:", payload); // Debug log

      try {
        const res = await fetch("http://localhost:8080/lecturer/component/save", {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify(payload),
        });

        if (!res.ok) {
          const errData = await res.json();
          throw new Error(errData.message || errData.error || "Server error");
        }
        this.showSuccessToast("✅ Component saved successfully.");

        this.showComponentModal = false;

        await this.fetchComponents();

        await fetch("http://localhost:8080/lecturer/sync-student-marks", { method: "POST" });

        await this.fetchStudents();
      } catch (error) {
        this.showErrorToast("⚠️ Total Weight 70% only " + error.message);
      }
    },

    async confirmDelete() {
      const name = this.pendingDeleteComponent;
      this.pendingDeleteComponent = null;
      this.showDeleteConfirm = false;

      try {
        const res = await fetch("http://localhost:8080/lecturer/component/delete", {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({ name }),
        });

        if (!res.ok) {
          const errData = await res.json();
          throw new Error(errData.message || errData.error || "Failed to delete component");
        }
        this.showSuccessToast("🗑️ Component deleted successfully.");
        await this.fetchComponents();
        await fetch("http://localhost:8080/lecturer/sync-student-marks", { method: "POST" });
        await this.fetchStudents();
      } catch (error) {
        this.showErrorToast("⚠️ Server error occurred: " + error.message);
      }
    },
    promptDeleteComponent(name) {
      this.pendingDeleteComponent = name;
      this.showDeleteConfirm = true;
    },
    showSuccessToast(message) {
      this.toastType = "success";
      this.toastMessage = message;
      this.showToast = true;
      setTimeout(() => this.showToast = false, 3000);
    },
    showErrorToast(message) {
      this.toastType = "error";
      this.toastMessage = message;
      this.showToast = true;
      setTimeout(() => this.showToast = false, 3000);
    }
  },
  mounted() {
    this.fetchComponents();
    this.fetchStudents();
    const userData = JSON.parse(sessionStorage.getItem('userData'));
    if (userData?.role === 'lecturer') {
      this.lecturerName = userData.name || 'Guest';
    }
  },
};
</script>

<style scoped>
.custom-toast {
  position: fixed;
  bottom: 30px;
  left: 50%;
  transform: translateX(-50%);
  color: white;
  padding: 12px 20px;
  border-radius: 10px;
  font-weight: 600;
  font-size: 1rem;
  box-shadow: 0 6px 20px rgba(0, 0, 0, 0.25);
  z-index: 10000;
  animation: fadeInOut 3s ease-in-out;
  white-space: nowrap;
}

.custom-toast.success {
  background: #27ae60;
}

.custom-toast.error {
  background: #e74c3c;
}

@keyframes fadeInOut {
  0% { opacity: 0; transform: translateY(30px) translateX(-50%); }
  10% { opacity: 1; transform: translateY(0) translateX(-50%); }
  90% { opacity: 1; transform: translateY(0) translateX(-50%); }
  100% { opacity: 0; transform: translateY(-30px) translateX(-50%); }
}

.modal-overlay {
  position: fixed;
  top: 0; left: 0; right: 0; bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 9998;
}

.modal {
  background: #fff;
  padding: 24px 32px;
  border-radius: 12px;
  width: 350px;
  box-shadow: 0 8px 20px rgba(0, 0, 0, 0.25);
  animation: fadeIn 0.3s ease-out;
}

.modal h3 {
  margin-bottom: 12px;
  font-size: 1.2rem;
}

.modal p {
  margin-bottom: 20px;
  font-size: 1rem;
}

.modal-buttons {
  display: flex;
  justify-content: flex-end;
  gap: 10px;
}

.delete-btn {
  background: #e74c3c;
  color: white;
  padding: 8px 14px;
  border: none;
  border-radius: 8px;
  font-weight: bold;
  cursor: pointer;
}

.cancel-btn {
  background: #bdc3c7;
  color: #2c3e50;
  padding: 8px 14px;
  border: none;
  border-radius: 8px;
  font-weight: bold;
  cursor: pointer;
}
</style>
