<template>
  <div class="page-bg">
    <div class="login-container">
      <div id="lock-icon">
        <h3>🔒</h3>
      </div>
      <div>
        <h2> Login</h2>
      </div>

      <form @submit.prevent="handleLogin" class="login-form">
        <div class="form-group">
          <label for="username">Username</label>
          <input id="username" v-model="username" type="text" autocomplete="username"
            placeholder="Please enter username" required />
        </div>
        <div class="form-group">
          <label for="password">Password</label>
          <input id="password" v-model="password" type="password" autocomplete="current-password"
            placeholder="Please enter password" required />
        </div>
        <button type="submit" class="login-btn">Login</button>
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
.page-bg {
  position: fixed;
  width: 100vw;
  height: 100vh;
  background-image: url('../assets/background.jpeg');
  background-size: 100% 100%;
  background-repeat: no-repeat;
  background-position: center;
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;
}

.login-container {
  width: 25em;
  height: auto;
  padding: 1.5rem;
  background: linear-gradient(135deg, #eaf2ff, #ffffff);
  border-radius: 20px;
  box-shadow: 0 10px 25px rgba(0, 90, 255, 0.1);
  display: flex;
  justify-content: center;
  flex-direction: column;
  align-items: center;
}

.login-form {
  display: flex;
  flex-direction: column;
  gap: 1.8rem;
  width: 100%;
}

.form-group {
  display: flex;
  flex-direction: column;
  width: 100%;
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

.login-btn {
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

.login-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 16px rgba(58, 108, 220, 0.6);
}

.login-btn:active {
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