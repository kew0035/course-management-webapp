<template>
  <div class="page-bg">
  <div class="login-container">
    <h2>Login</h2>
    <form @submit.prevent="handleLogin" class="login-form">
      <div class="form-group">
        <label for="username">Username</label>
        <input
          id="username"
          v-model="username"
          type="text"
          autocomplete="username"
          placeholder="Please enter username"
          required
        />
      </div>
      <div class="form-group">
        <label for="password">Password</label>
        <input
          id="password"
          v-model="password"
          type="password"
          autocomplete="current-password"
          placeholder="Please enter password"
          required
        />
      </div>
      <button type="submit" class="btn">Login</button>
    </form>
    <p v-if="errorMessage" class="error-msg">{{ errorMessage }}</p>
  </div>
</div>
</template>

<script>
export default {
  name: "LoginForm",
  data() {
    return {
      username: "",
      password: "",
      errorMessage: "",
    };
  },
  methods: {
    async handleLogin() {
      this.errorMessage = "";
      try {
        const res = await fetch("http://localhost:8080/login", {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          credentials: 'include',
          body: JSON.stringify({
            username: this.username,
            password: this.password,
          }),
        });

        if (!res.ok) {
          const errorData = await res.json();
          this.errorMessage = errorData.message || errorData.data?.message || "Login Failed";
          return;
        }

        const data = await res.json();       
        sessionStorage.setItem('userData', JSON.stringify({
          id: data.user_id, 
          name: data.name,
          role: data.role,
          username: data.username,
        }));

        switch (data.role) {
          case "student":
            this.$router.push("/student");
            break;
          case "lecturer":
            this.$router.push("/lecturer");
            break;
          case "advisor":
            this.$router.push("/advisor");
            break;
          case "admin":
            this.$router.push("/admin");
            break;
          default:
            this.$router.push("/");
        }
      } catch (error) {
        this.errorMessage = "Network error or server not responding";
        console.error(error);
      }
    },
  },
};
</script>

<style scoped>
html, body {
  margin: 0;
  padding: 0;
  height: 100vh;
  width: 100vw;
  overflow: hidden;
}

.page-bg {
  background-color: red;
  width: auto;
  height: 100vh;
  overflow: hidden;
  /* background-image: url('../assets/background.jpeg'); */
  background-size: cover;
  background-repeat: no-repeat;
  background-position: center;
  display: flex;
  align-items: center;
  justify-content: center;
}

.login-container {
  max-width: 420px;
  width: 90%; /* 确保不会超出屏幕宽度 */
  padding: 3rem 2.5rem;
  margin: 0 auto;
  background: linear-gradient(135deg, #eaf2ff, #ffffff);
  border-radius: 20px;
  box-shadow: 0 10px 25px rgba(0, 90, 255, 0.1);
  font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
  transition: all 0.3s ease;
}



.login-container:hover {
  box-shadow: 0 14px 40px rgba(0, 90, 255, 0.18);
}

h2 {
  text-align: center;
  color: #2c3e50;
  margin-bottom: 2rem;
  font-weight: 800;
  font-size: 2.2rem;
  letter-spacing: 0.8px;
  position: relative;
}

h2::before {
  content: "🔒";
  font-size: 1.5rem;
  position: absolute;
  left: 50%;
  transform: translateX(-50%) translateY(-160%);
}

.login-form {
  display: flex;
  flex-direction: column;
  gap: 1.8rem;
}

.form-group {
  display: flex;
  flex-direction: column;
}

.form-group label {
  font-weight: 600;
  color: #395692;
  margin-bottom: 0.6rem;
  font-size: 0.95rem;
}

input[type="text"],
input[type="password"] {
  width: 90%;
  padding: 0.85rem 1rem;
  border-radius: 12px;
  border: 2px solid #c7dbff;
  font-size: 1.05rem;
  background-color: #f9fbff;
  color: #213659;
  transition: border-color 0.3s, box-shadow 0.3s;
}

input[type="text"]::placeholder,
input[type="password"]::placeholder {
  color: #b0c9f7;
  font-weight: 400;
}

input[type="text"]:focus,
input[type="password"]:focus {
  outline: none;
  border-color: #4080f0;
  box-shadow: 0 0 8px rgba(64, 128, 240, 0.3);
  background-color: #ffffff;
}

.btn {
  width: 100%;
  background: linear-gradient(90deg, #4a90e2, #3578e5);
  color: white;
  padding: 0.85rem;
  border-radius: 14px;
  border: none;
  font-weight: 700;
  font-size: 1.15rem;
  cursor: pointer;
  letter-spacing: 0.05em;
  box-shadow: 0 4px 12px rgba(58, 108, 220, 0.4);
  transition: transform 0.2s ease, box-shadow 0.3s ease;
}

.btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 16px rgba(58, 108, 220, 0.6);
}

.btn:active {
  transform: translateY(0);
  box-shadow: 0 3px 8px rgba(58, 108, 220, 0.5);
}

.error-msg {
  margin-top: 1.5rem;
  color: #d94848;
  font-weight: 600;
  text-align: center;
  background: #ffe5e5;
  padding: 0.75rem 1rem;
  border-radius: 12px;
  box-shadow: 0 0 6px #d9484844;
  user-select: none;
}

@media (max-width: 480px) {
  .login-container {
    margin: 4vh 1rem;
    padding: 2rem 1.5rem;
  }

  h2 {
    font-size: 1.7rem;
  }

  .btn {
    font-size: 1rem;
  }
}

</style>