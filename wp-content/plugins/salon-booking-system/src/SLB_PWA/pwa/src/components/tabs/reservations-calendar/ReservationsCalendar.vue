<template>
  <div>
    <h5 class="title">
      {{ $t("reservationsCalendarTitle") }}
    </h5>
    <div class="search">
      <font-awesome-icon icon="fa-solid fa-magnifying-glass" class="search-icon" />
      <b-form-input v-model="search" class="search-input"></b-form-input>
      <font-awesome-icon
        icon="fa-solid fa-circle-xmark"
        class="clear"
        @click="search = ''"
        v-if="search"
      />
    </div>
    <b-row>
      <b-col sm="12">
        <div class="calendar">
          <Datepicker
            v-model="date"
            inline
            autoApply
            noSwipe
            :enableTimePicker="false"
            :monthChangeOnScroll="false"
            @updateMonthYear="handleMonthYear"
            :locale="locale"
          >
            <template #day="{ day, date }">
              <template v-if="isDayWithBookings(date)">
                <div class="day day-with-bookings">{{ day }}</div>
              </template>
              <template v-else-if="isDayFullBooked(date)">
                <div class="day day-full-booked">{{ day }}</div>
              </template>
              <template v-else-if="isAvailableBookings(date)">
                <div class="day day-available-book">{{ day }}</div>
              </template>
              <template v-else>
                <div class="day day-disable-book">{{ day }}</div>
              </template>
            </template>
          </Datepicker>
          <div class="spinner-wrapper" v-if="isLoadingCalendar"></div>
          <b-spinner variant="primary" v-if="isLoadingCalendar"></b-spinner>
        </div>
      </b-col>
    </b-row>
    <b-row>
      <b-col sm="12">
        <div class="attendants">
          <b-button
            v-for="attendant in attendants"
            :key="attendant.id"
            variant="outline-primary"
            :pressed="filterAttendant === attendant.id"
            @click="
              filterAttendant === attendant.id
                ? (filterAttendant = '')
                : (filterAttendant = attendant.id)
            "
          >
            {{ attendant.name }}
          </b-button>
        </div>
      </b-col>
    </b-row>
    <div class="slots">
      <b-spinner variant="primary" v-if="isLoadingTimeslots"></b-spinner>
      <div v-else-if="timeslots.length > 0">
        <template v-for="(timeslot, index) in timeslots" :key="timeslot">
          <BookingSlot
            :timeslot="timeslot"
            @add="add(timeslot)"
            :isAvailable="isAvailable(timeslot, timeslot)"
          />
          <template v-if="getBookingsListByTime(timeslot, timeslots[index + 1]).length > 0">
            <BookingItem
              v-for="booking in getBookingsListByTime(timeslot, timeslots[index + 1])"
              :key="booking.id"
              :booking="booking"
              @deleteItem="deleteItem(booking.id)"
              @showDetails="showDetails(booking)"
            />
          </template>
          <BookingBlockSlot
            v-else-if="index < timeslots.length - 1"
            :start="timeslots[index]"
            :end="timeslots[index + 1]"
            :date="date"
            :shop="shop"
            @lock="lock"
            @unlock="unlock"
            :isLock="isLock(timeslots[index], timeslots[index + 1])"
          />
        </template>
      </div>
      <span v-else>{{ $t("noResultTimeslotsLabel") }}</span>
    </div>
  </div>
</template>

<script>
import BookingSlot from "./BookingSlot.vue";
import BookingBlockSlot from "./BookingBlockSlot.vue";
import BookingItem from "./../upcoming-reservations/BookingItem.vue";

export default {
  name: "ReservationsCalendar",
  props: {
    shop: {
        default: function () {
            return {};
        },
    }
  },
  mounted() {
    this.load()
    setTimeout(() => {
      window.document
        .querySelectorAll(".dp__calendar")[0]
        .appendChild(window.document.querySelectorAll(".spinner-wrapper")[0]);
      window.document
        .querySelectorAll(".dp__calendar")[0]
        .appendChild(window.document.querySelectorAll(".calendar .spinner-border")[0]);
    }, 0);
  },
  data: function () {
    return {
      date: new Date(),
      timeslots: [],
      isLoadingTimeslots: false,
      lockedTimeslots: [],
      availabilityStats: [],
      bookingsList: [],
      filterAttendant: "",
      search: "",
      timeout: null,
      availabilityIntervals: {},
      isLoadingCalendar: false,
    };
  },
  watch: {
    date() {
      this.loadLockedTimeslots();
      this.loadBookingsList();
      this.loadAvailabilityIntervals();
    },
    search(newVal) {
      if (newVal) {
        this.loadSearchBookingsList();
      } else {
        this.loadBookingsList();
      }
    },
    timeslots() {
      this.loadAvailabilityIntervals();
    },
    lockedTimeslots() {
      if (this.timeslots.length > 0) {
        this.loadAvailabilityIntervals();
      }
    },
    shop() {
        this.load()
    },
  },
  computed: {
    attendants() {
      var attendants = {};
      attendants[0] = {
        id: "",
        name: this.$t("allTitle"),
      };
      this.bookingsList.forEach((booking) => {
        booking.services.forEach((service) => {
          if (service.assistant_id > 0) {
            attendants[service.assistant_id] = {
              id: service.assistant_id,
              name: service.assistant_name,
            };
          }
        });
      });
      return Object.values(attendants).length > 1 ? Object.values(attendants) : [];
    },
    filteredBookingsList() {
      return this.bookingsList.filter((booking) => {
        var existsAttendant = false;
        booking.services.forEach((service) => {
          if (this.filterAttendant === service.assistant_id) {
            existsAttendant = true;
          }
        });
        return this.filterAttendant === "" || existsAttendant;
      });
    },
  },
  methods: {
    load() {
        this.loadTimeslots();
        this.loadLockedTimeslots();
        var date = this.date,
          y = date.getFullYear(),
          m = date.getMonth();
        var firstDate = new Date(y, m, 1);
        var lastDate = new Date(y, m + 1, 0);
        this.loadAvailabilityStats(firstDate, lastDate);
        this.loadBookingsList();
    },
    add(timeslot) {
      this.$emit("add", this.date, timeslot ? timeslot : this.timeslots[0]);
    },
    lock(lockedItems) {
      this.lockedTimeslots = lockedItems;
    },
    unlock(lockedItems) {
      this.lockedTimeslots = lockedItems;
    },
    loadTimeslots() {
      this.isLoadingTimeslots = true;
      this.axios
        .get("calendar/intervals", {params: {shop: this.shop ? this.shop.id : null}})
        .then((response) => {
          this.timeslots = response.data.items;
        })
        .finally(() => {
          this.isLoadingTimeslots = false;
        });
    },
    loadLockedTimeslots() {
      this.isLoadingTimeslots = true;
      this.axios
        .get("holiday-rules", {
          params: { date: this.moment(this.date).format("YYYY-MM-DD"), shop: this.shop ? this.shop.id : null },
        })
        .then((response) => {
          this.lockedTimeslots = response.data.items;
        })
        .finally(() => {
          this.isLoadingTimeslots = false;
        });
    },
    loadAvailabilityStats(from_date, to_date) {
      this.isLoadingCalendar = true;
      this.axios
        .get("availability/stats", {
          params: {
            from_date: this.moment(from_date).format("YYYY-MM-DD"),
            to_date: this.moment(to_date).format("YYYY-MM-DD"),
            shop: this.shop ? this.shop.id : null,
          },
        })
        .then((response) => {
          this.availabilityStats = response.data.stats;
        })
        .finally(() => {
          this.isLoadingCalendar = false;
        });
    },
    loadBookingsList() {
      this.isLoadingTimeslots = true;
      this.bookingsList = [];
      this.axios
        .get("bookings", {
          params: {
            start_date: this.moment(this.date).format("YYYY-MM-DD"),
            end_date: this.moment(this.date).format("YYYY-MM-DD"),
            per_page: -1,
            statuses: [
                'sln-b-pendingpayment',
                'sln-b-pending',
                'sln-b-paid',
                'sln-b-paylater',
                'sln-b-canceled',
                'sln-b-confirmed'
            ],
            shop: this.shop ? this.shop.id : null,
          },
        })
        .then((response) => {
          this.bookingsList = response.data.items;
        })
        .finally(() => {
          this.isLoadingTimeslots = false;
        });
    },
    loadAvailabilityIntervals() {
      this.axios
        .post("availability/intervals", {
          date: this.moment(this.date).format("YYYY-MM-DD"),
          time: this.timeslots[0],
          shop: this.shop ? this.shop.id : 0,
        })
        .then((response) => {
          this.availabilityIntervals = response.data.intervals;
        });
    },
    isAvailable(start) {
      if (
        this.availabilityIntervals.universalSuggestedDate !==
        this.moment(this.date).format("YYYY-MM-DD")
      ) {
        return false;
      }
      if (Object.values(this.availabilityIntervals.times).indexOf(start) > -1) {
        return true;
      }
      return false;

    },
    isLock(start, end) {
      var result = false;
      this.lockedTimeslots.forEach((i) => {
        var fromDateTime = this.moment(
          i.from_date + " " + i.from_time,
          "YYYY-MM-DD HH:mm"
        ).unix();
        var toDateTime = this.moment(
          i.to_date + " " + i.to_time,
          "YYYY-MM-DD HH:mm"
        ).unix();
        var date = this.moment(this.date).format("YYYY-MM-DD");
        var currentFromDateTime = this.moment(
          date + " " + start,
          "YYYY-MM-DD HH:mm"
        ).unix();
        var currentToDateTime = this.moment(date + " " + end, "YYYY-MM-DD HH:mm").unix();
        if (
          fromDateTime <= currentFromDateTime &&
          toDateTime >= currentToDateTime &&
          toDateTime > currentFromDateTime
        ) {
          result = true;
        }
      });
      return result;
    },
    handleMonthYear(obj) {
      var firstDate = new Date(obj.year, obj.month, 1);
      var lastDate = new Date(obj.year, obj.month + 1, 0);
      this.loadAvailabilityStats(firstDate, lastDate);
    },
    isDayWithBookings(date) {
      var result = false;
      this.availabilityStats.forEach((i) => {
        if (
          i.date === this.moment(date).format("YYYY-MM-DD") &&
          i.data &&
          i.data.bookings > 0
        ) {
          result = true;
        }
      });
      return result;
    },
    isAvailableBookings(date) {
      var result = false;
      this.availabilityStats.forEach((i) => {
        if (
          i.date === this.moment(date).format("YYYY-MM-DD") &&
          i.available &&
          !i.full_booked
        ) {
          result = true;
        }
      });
      return result;
    },
    isDayFullBooked(date) {
      var result = false;
      this.availabilityStats.forEach((i) => {
        if (i.date === this.moment(date).format("YYYY-MM-DD") && i.full_booked) {
          result = true;
        }
      });
      return result;
    },
    getBookingsListByTime(timeStart, timeEnd) {
      return this.filteredBookingsList.filter((i) => this.moment(i.time, 'HH:mm').unix() >= this.moment(timeStart, 'HH:mm').unix() && (!timeEnd || this.moment(i.time, 'HH:mm').unix() < this.moment(timeEnd, 'HH:mm').unix())).sort((a, b) => this.moment(a.time, 'HH:mm').unix() - this.moment(b.time, 'HH:mm').unix());
    },
    deleteItem(id) {
      this.axios.delete("bookings/" + id).then(() => {
        this.bookingsList = this.bookingsList.filter((item) => item.id !== id);
      });
    },
    showDetails(booking) {
      this.$emit("showItem", booking);
    },
    loadSearchBookingsList() {
      this.timeout && clearTimeout(this.timeout);
      this.timeout = setTimeout(() => {
        this.isLoadingTimeslots = true;
        this.bookingsList = [];
        this.axios
          .get("bookings", {
            params: {
              start_date: this.moment(this.date).format("YYYY-MM-DD"),
              end_date: this.moment(this.date).format("YYYY-MM-DD"),
              search: this.search,
              per_page: -1,
              statuses: [
                'sln-b-pendingpayment',
                'sln-b-pending',
                'sln-b-paid',
                'sln-b-paylater',
                'sln-b-canceled',
                'sln-b-confirmed'
              ],
              shop: this.shop ? this.shop.id : null,
            },
          })
          .then((response) => {
            this.bookingsList = response.data.items;
          })
          .finally(() => {
            this.isLoadingTimeslots = false;
          });
      }, 1000);
    },
  },
  components: {
    BookingSlot,
    BookingBlockSlot,
    BookingItem,
  },
  emits: ["add", "showItem"],
};
</script>

<style scoped>
.calendar,
.slots,
.calendar .btn,
.attendants,
.search {
  margin-top: 1.5rem;
}
.calendar :deep(.dp__menu) {
  margin: 0 auto;
}
:deep(.dp__cell_inner) {
  height: 45px;
  width: 45px;
}
:deep(.dp__calendar_header_item) {
  height: 30px;
  width: 45px;
}
.day {
  display: flex;
  align-items: center;
  text-align: center;
  justify-content: center;
  border-radius: 20px;
  height: 35px;
  padding: 10px;
  width: 35px;
  border: 1px solid #c7ced9;
  box-sizing: border-box;
  position: relative;
}
.day-with-bookings {
  border-color: #04409F;
  color: #04409F;
  border: 1px solid;
  font-weight:400;
}
.day-full-booked {
  border-color: #c7ced9;
  color: #c7ced9;
}
.day-available-book {
  color: #04409f;
  border: 3px solid #04409f;
  font-weight: 600;
}
.day-disable-book {
  border-color: #c7ced9;
  color: #c7ced9;
}
.attendants .btn {
  margin-right: 20px;
}
.search {
  position: relative;
}
.clear {
  position: absolute;
  top: 10px;
  z-index: 1000;
  right: 15px;
  cursor: pointer;
}
.title {
  text-align: left;
  font-weight: bold;
  color: #322d38;
  font-size: 22px;
}
.search-icon {
  position: absolute;
  z-index: 1000;
  top: 12px;
  left: 15px;
  color: #7f8ca2;
}
.search .search-input {
  padding-left: 40px;
  padding-right: 20px;
  border-radius: 30px;
  border-color: #7f8ca2;
}
.attendants .btn {
  border-radius: 30px;
  padding: 4px 20px;
  background-color: #e1e6ef9b;
  color: #04409f;
  border-color: #7f8ca2;
}
.attendants .btn:hover,
.attendants .btn.active {
  color: #04409f;
  background-color: #7f8ca2;
  border-color: #7f8ca2;
}
.attendants {
  white-space: nowrap;
  overflow: auto;
}
.attendants::-webkit-scrollbar {
  display: none;
}
:deep(.dp__month_year_select) {
  width: 100%;
  pointer-events: none;
}
:deep(.dp__month_year_select + .dp__month_year_select) {
  display: none;
}
:deep(.dp__menu),
:deep(.dp__menu:focus) {
  border: none;
}
:deep(.dp__calendar_header_separator) {
  height: 0;
}
:deep(.dp__today) {
  border: none;
}
:deep(.dp__active_date) {
  background: none;
}
:deep(.dp__active_date) .day {
  background: #1976d2;
  color: #fff;
}
.spinner-wrapper {
  width: 100%;
  height: 100%;
  position: absolute;
  background-color: #e0e0e0d1;
  opacity: 0.5;
  top: 0;
}
.calendar .spinner-border {
  position: absolute;
  top: 45%;
  left: 45%;
}
</style>
