<template>
  <div>
    <button :class="['review-btn', { submitted: appeal.status }]" @click="openDialog">
      {{ appeal.status ? 'Submitted' : 'Request Review' }}
    </button>

    <div v-if="showDialog" class="modal">
      <div class="modal-content">
        <h2>Appeal for {{ componentName }}</h2>

        <div v-if="appeal.status">
          <p><strong>Status:</strong> {{ appeal.status }}</p>
          <p><strong>Your Reason:</strong></p>
          <p class="readonly">{{ appeal.reason }}</p>
        </div>

        <div v-else>
          <textarea v-model="reason" placeholder="Enter reason for review"></textarea>
          <button @click="submitAppeal" class="submit-btn">Submit</button>
        </div>

        <button class="close-btn" @click="showDialog = false">Close</button>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  props: {
    componentName: String,
    scmId: {
      type: Number,
      required: true
    },
    courseId: {
      type: Number,
      required: true
    }
  },
  data() {
    return {
      showDialog: false,
      reason: '',
      appeal: {
        status: null,
        reason: ''
      }
    };
  },
  methods: {
    async fetchAppealStatus() {
      try {
        const res = await fetch(`http://localhost:8080/student/appeal?scm_id=${this.scmId}&course_id=${this.courseId}`, {
          credentials: 'include'
        });

        const data = await res.json();
        if (res.ok && data.status) {
          this.appeal = data;
        }
      } catch (err) {
        console.error('Failed to fetch appeal status:', err);
      }
    },
    openDialog() {
      this.showDialog = true;
    },
    async submitAppeal() {
      const payload = {
        scm_id: this.scmId,
        course_id: this.courseId,
        reason: this.reason
      };

      console.log("🟨 Submit Payload:", payload); // ✅ Debug log

      try {
        const res = await fetch('http://localhost:8080/student/appeal', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(payload),
          credentials: 'include'
        });

        const result = await res.json();
        console.log("🟩 Submit Result:", result);

        if (!res.ok) {
          alert(result.message || 'Error occurred');
          return;
        }

        this.appeal = {
          status: 'pending',
          reason: this.reason
        };
        this.showDialog = true;
      } catch (err) {
        console.error(err);
        alert('Network error or server unavailable');
      }
    }
  },
  watch: {
    scmId: 'fetchAppealStatus',
    courseId: 'fetchAppealStatus',
  }
};
</script>


<style scoped>
.review-btn {
  padding: 6px 12px;
  background-color: #3a86ff;
  color: white;
  border: none;
  border-radius: 6px;
  font-weight: bold;
  cursor: pointer;
}

.review-btn.submitted {
  background-color: #ccc;
  color: #444;
  cursor: pointer;
}

.modal {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.4);
  display: flex;
  justify-content: center;
  align-items: center;
}

textarea {
  width: 100%;
  height: 80px;
  margin-top: 10px;
}

.readonly {
  background: #f5f5f5;
  padding: 10px;
  border-radius: 4px;
  font-style: italic;
}
</style>
