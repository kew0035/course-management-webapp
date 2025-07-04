<template>
  <div class="modal">
    <div class="modal-content">
      <h3 class="modal-title">📄 Appeal Review</h3>

      <div class="appeal-info">
        <p><strong>👤 Student:</strong> {{ student.name }} ({{ student.matricNo }})</p>
        <p><strong>📌 Appeal For:</strong> {{ component }}</p>
        <p><strong>📝 Reason:</strong> {{ appeal.reason }}</p>
      </div>

      <div class="decision-buttons">
        <button class="approve-btn" @click="$emit('respond', 'approved')">✔ Accept</button>
        <button class="reject-btn" @click="$emit('respond', 'rejected')">✖ Reject</button>
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
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  max-width: 100%;
  border-radius: 0;
  background: rgba(0, 0, 0, 0.4);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 9999;
}

.modal-content {
  background: #ffffff;
  padding: 30px;
  border-radius: 12px;
  width: 420px;
  max-width: 95%;
  box-shadow: 0 15px 30px rgba(0, 0, 0, 0.2);
  position: relative;
  font-family: 'Segoe UI', sans-serif;
}

.modal-title {
  text-align: center;
  font-size: 22px;
  font-weight: 700;
  color: #2c3e50;
  margin-bottom: 20px;
}

.appeal-info p {
  margin: 10px 0;
  font-size: 16px;
  color: #333;
}

.decision-buttons {
  display: flex;
  justify-content: space-between;
  margin-top: 25px;
}

.approve-btn,
.reject-btn {
  flex: 1;
  font-size: 16px;
  font-weight: bold;
  padding: 10px 15px;
  border: none;
  border-radius: 8px;
  transition: background 0.3s;
  cursor: pointer;
  margin: 0 5px;
}

.approve-btn {
  background-color: #2ecc71;
  color: white;
}

.approve-btn:hover {
  background-color: #27ae60;
}

.reject-btn {
  background-color: #e74c3c;
  color: white;
}

.reject-btn:hover {
  background-color: #c0392b;
}

.close-btn {
  background: #7f8c8d;
  color: white;
  border: none;
  padding: 8px 14px;
  border-radius: 6px;
  font-weight: bold;
  float: right;
  margin-top: 20px;
  cursor: pointer;
  transition: background 0.3s;
}

.close-btn:hover {
  background-color: #606d70;
}
</style>