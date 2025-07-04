<template>
  <div class="modal">
    <div class="modal-content">
      <h3>Appeal Review</h3>
      <p><strong>Student:</strong> {{ student.name }} ({{ student.matricNo }})</p>
      <p><strong>Appeal For:</strong> {{ component }}</p>
      <p><strong>Reason:</strong> {{ appeal.reason }}</p>
      
      <div class="decision-buttons">
        <div><button class="approve-btn" @click="$emit('respond', 'approved')">✔ Accept</button></div>
        <div><button class="reject-btn" @click="$emit('respond', 'rejected')">✖ Reject</button></div>
      </div>
      <button class="close-btn" @click="$emit('close')">Close</button>
    </div>
  </div>
</template>

<script>
export default {
  props: {
    student: Object,
    component: String,
    appeal: Object
  }
};
</script>


<style scoped>
.modal {
  position: fixed;
  top: 0; left: 0;
  width: 100%; height: 100%;
  background: rgba(0, 0, 0, 0.4);
  display: flex; justify-content: center; align-items: center;
  z-index: 9999;
}

.modal-content {
  background: white;
  padding: 20px;
  border-radius: 8px;
  width: 400px;
  max-width: 90%;
  box-shadow: 0 10px 30px rgba(0,0,0,0.2);
}

.decision-buttons {
  display: flex;
  justify-content: flex-start;
  gap:1em;
}

.approve-btn {
  background-color: #27ae60;
  color: white;
  padding: 6px 14px;
  border: none;
  border-radius: 6px;
  font-weight: bold;
}

.reject-btn {
  background-color: #e74c3c;
  color: white;
  padding: 6px 14px;
  border: none;
  border-radius: 6px;
  font-weight: bold;
}

.close-btn {
  background: #888;
  color: white;
  padding: 6px 14px;
  border: none;
  border-radius: 6px;
  margin-top: 12px;
  float: right;
}

.status-approved {
  color: #27ae60;
  font-weight: bold;
}

.status-rejected {
  color: #e74c3c;
  font-weight: bold;
}

.status-pending {
  color: #f39c12;
  font-weight: bold;
}
</style>