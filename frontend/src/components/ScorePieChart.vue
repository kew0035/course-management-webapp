<template>
  <div>
    <h4>Score Distribution</h4>
    <PieChart v-if="chartData.labels && chartData.labels.length > 0" :data="chartData" :options="chartOptions" />
    <p v-else>No data available</p>
  </div>
</template>


<script>
import { Pie } from "vue-chartjs";
import {
  Chart as ChartJS,
  Title,
  Tooltip,
  Legend,
  ArcElement,
} from "chart.js";

ChartJS.register(Title, Tooltip, Legend, ArcElement);

export default {
  name: "ScorePieChart",
  components: {
    PieChart: Pie,
  },
  props: {
    distribution: {
      type: Object,
      required: false,
      default: () => ({}),
    },
  },
  data() {
    return {
      defaultChartData: {
        labels: [],
        datasets: [],
      },
    };
  },
  computed: {
    chartData() {
      if (!this.distribution || Object.keys(this.distribution).length === 0) {
        return this.defaultChartData;
      }
      const labels = Object.keys(this.distribution);
      const data = Object.values(this.distribution);
      if (labels.length === 0 || data.length === 0) {
        return this.defaultChartData;
      }
      return {
        labels,
        datasets: [
          {
            data,
            backgroundColor: [
              "#3a6ee8",
              "#4a90e2",
              "#5aa0e7",
              "#6ab0ec",
              "#7abff1",
              "#8bceff",
              "#9cdcff",
              "#adebff",
              "#beffff",
              "#cfffff",
            ],
          },
        ],
      };
    },
    chartOptions() {
      return {
        responsive: true,
        plugins: {
          legend: { position: "right" },
          title: { display: false },
        },
      };
    },
  },
};
</script>
