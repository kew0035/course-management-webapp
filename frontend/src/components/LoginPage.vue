<template>
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
          username: data.username
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
.login-container {
  max-width: 400px;
  margin: 6vh auto;
  padding: 2.5rem 3rem;
  background: linear-gradient(135deg, #e0f0ff, #ffffff);
  border-radius: 16px;
  box-shadow: 0 10px 25px rgba(72, 135, 255, 0.15);
  font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
  transition: box-shadow 0.3s ease;
}
.login-container:hover {
  box-shadow: 0 15px 40px rgba(72, 135, 255, 0.3);
}

h2 {
  text-align: center;
  color: #305fbc;
  margin-bottom: 2rem;
  font-weight: 700;
  font-size: 2rem;
  letter-spacing: 1.1px;
  text-shadow: 1px 1px 3px #a0b7f7;
}

.login-form {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.form-group label {
  font-weight: 600;
  color: #395692;
  margin-bottom: 0.6rem;
  display: block;
  letter-spacing: 0.02em;
}

input[type="text"],
input[type="password"] {
  width: 90%;
  padding: 0.75rem 1.2rem;
  border-radius: 12px;
  border: 2px solid #a9c4ff;
  font-size: 1.05rem;
  transition: border-color 0.3s ease, box-shadow 0.3s ease;
  background-color: #f6faff;
  color: #213659;
  box-shadow: inset 0 1px 4px rgba(0, 0, 0, 0.05);
}

input[type="text"]::placeholder,
input[type="password"]::placeholder {
  color: #aac8ff;
  font-weight: 400;
  font-style: italic;
}

input[type="text"]:focus,
input[type="password"]:focus {
  outline: none;
  border-color: #3f72f0;
  box-shadow: 0 0 10px #3f72f055, inset 0 0 6px #d0e0ffcc;
  background-color: #ffffff;
}

.btn {
  background: linear-gradient(90deg, #4a90e2, #3578e5);
  color: white;
  padding: 0.85rem 0;
  border-radius: 14px;
  border: none;
  font-weight: 700;
  font-size: 1.2rem;
  cursor: pointer;
  letter-spacing: 0.05em;
  box-shadow: 0 4px 10px rgba(58, 108, 220, 0.6);
  transition: background 0.35s ease, box-shadow 0.3s ease;
  user-select: none;
}

.btn:hover {
  background: linear-gradient(90deg, #3578e5, #4a90e2);
  box-shadow: 0 6px 16px rgba(58, 108, 220, 0.8);
  transform: translateY(-2px);
}

.btn:active {
  transform: translateY(0);
  box-shadow: 0 3px 8px rgba(58, 108, 220, 0.6);
}

.error-msg {
  margin-top: 1.5rem;
  color: #d94848;
  font-weight: 700;
  text-align: center;
  background: #ffe5e5;
  padding: 0.75rem 1rem;
  border-radius: 12px;
  box-shadow: 0 0 8px #d9484844;
  user-select: none;
  letter-spacing: 0.02em;
}

@media (max-width: 420px) {
  .login-container {
    margin: 4vh 1rem;
    padding: 2rem 2rem;
  }

  h2 {
    font-size: 1.6rem;
  }

  .btn {
    font-size: 1.05rem;
  }
}
</style>