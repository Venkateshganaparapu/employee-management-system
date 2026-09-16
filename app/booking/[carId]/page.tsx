"use client";

import { useState } from "react";
import { useParams, useRouter } from "next/navigation";
import { motion } from "framer-motion";
import Image from "next/image";
import Link from "next/link";
import { ArrowLeft, CalendarDays, MapPin, User, CreditCard, AlertCircle } from "lucide-react";
import { getCarById } from "@/lib/mock-data";
import { cn, formatCurrency, calculateDays } from "@/lib/utils";
import { AnimatedPage } from "@/components/AnimatedPage";
import toast from "react-hot-toast";

const LOCATIONS = [
  "Downtown Hub",
  "Airport Terminal A",
  "Airport Terminal B",
  "North Hub",
  "South Hub",
  "VIP Lounge",
];

export default function BookingPage() {
  const params = useParams();
  const router = useRouter();
  const car = getCarById(params.carId as string);

  const today = new Date().toISOString().split("T")[0];
  const tomorrow = new Date(Date.now() + 86400000).toISOString().split("T")[0];

  const [form, setForm] = useState({
    startDate: today,
    endDate: tomorrow,
    pickupLocation: LOCATIONS[0],
    dropoffLocation: LOCATIONS[0],
    firstName: "",
    lastName: "",
    email: "",
    phone: "",
    notes: "",
    agree: false,
  });
  const [step, setStep] = useState(1); // 1: details, 2: review, 3: success
  const [loading, setLoading] = useState(false);

  if (!car) return (
    <div className="min-h-screen pt-20 flex items-center justify-center">
      <p className="text-dark-400">Car not found. <Link href="/cars" className="text-brand-400">Go back</Link></p>
    </div>
  );

  const days = calculateDays(new Date(form.startDate), new Date(form.endDate));
  const subtotal = car.pricePerDay * days;
  const tax = subtotal * 0.1;
  const total = subtotal + tax;

  const update = (field: string, value: string | boolean) =>
    setForm((prev) => ({ ...prev, [field]: value }));

  const handleStep1 = () => {
    if (!form.startDate || !form.endDate) return toast.error("Please select dates");
    if (form.endDate <= form.startDate) return toast.error("End date must be after start date");
    setStep(2);
    window.scrollTo({ top: 0, behavior: "smooth" });
  };

  const handleConfirm = async () => {
    if (!form.agree) return toast.error("Please agree to the terms");
    setLoading(true);
    // Simulate payment processing
    await new Promise((r) => setTimeout(r, 2000));
    setLoading(false);
    setStep(3);
    window.scrollTo({ top: 0, behavior: "smooth" });
    toast.success("Booking confirmed!");
  };

  // ── Step 3: Success ──
  if (step === 3) {
    return (
      <AnimatedPage className="min-h-screen pt-20 flex items-center justify-center px-4">
        <motion.div
          initial={{ scale: 0.8, opacity: 0 }}
          animate={{ scale: 1, opacity: 1 }}
          transition={{ type: "spring", bounce: 0.4 }}
          className="max-w-md w-full text-center"
        >
          {/* Animated checkmark */}
          <motion.div
            initial={{ scale: 0 }}
            animate={{ scale: 1 }}
            transition={{ delay: 0.2, type: "spring", bounce: 0.5 }}
            className="w-24 h-24 rounded-full bg-green-500/10 border border-green-500/30 flex items-center justify-center mx-auto mb-6"
          >
            <motion.svg viewBox="0 0 50 50" className="w-12 h-12">
              <motion.path
                d="M 14 27 L 22 35 L 36 16"
                fill="none"
                stroke="#4ade80"
                strokeWidth={3}
                strokeLinecap="round"
                strokeLinejoin="round"
                initial={{ pathLength: 0 }}
                animate={{ pathLength: 1 }}
                transition={{ delay: 0.4, duration: 0.5 }}
              />
            </motion.svg>
          </motion.div>

          <h1 className="font-display text-3xl font-bold text-white mb-3">Booking Confirmed!</h1>
          <p className="text-dark-400 mb-2">
            Your {car.make} {car.model} is booked for {days} day{days > 1 ? "s" : ""}.
          </p>
          <p className="text-dark-500 text-sm mb-8">
            Confirmation sent to <span className="text-brand-400">{form.email || "your email"}</span>
          </p>

          <div className="card p-5 text-left mb-6">
            <div className="flex justify-between text-sm mb-2">
              <span className="text-dark-400">Booking Reference</span>
              <span className="text-white font-mono font-semibold">BK-{Math.random().toString(36).slice(2, 8).toUpperCase()}</span>
            </div>
            <div className="flex justify-between text-sm mb-2">
              <span className="text-dark-400">Pick-up Date</span>
              <span className="text-white">{form.startDate}</span>
            </div>
            <div className="flex justify-between text-sm mb-2">
              <span className="text-dark-400">Return Date</span>
              <span className="text-white">{form.endDate}</span>
            </div>
            <div className="flex justify-between text-sm font-semibold border-t border-dark-700 pt-2 mt-2">
              <span className="text-dark-200">Total Paid</span>
              <span className="text-brand-400">{formatCurrency(total)}</span>
            </div>
          </div>

          <div className="flex gap-3">
            <Link href="/account/bookings" className="btn-primary flex-1 justify-center">
              View Bookings
            </Link>
            <Link href="/cars" className="btn-secondary flex-1 justify-center">
              Rent Another
            </Link>
          </div>
        </motion.div>
      </AnimatedPage>
    );
  }

  return (
    <AnimatedPage className="min-h-screen pt-20">
      <div className="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {/* Back */}
        <button
          onClick={() => step === 1 ? router.push(`/cars/${car.id}`) : setStep(1)}
          className="inline-flex items-center gap-2 text-dark-400 hover:text-white transition-colors mb-6"
        >
          <ArrowLeft className="w-4 h-4" />
          {step === 1 ? "Back to Car" : "Back to Details"}
        </button>

        {/* Progress Steps */}
        <div className="flex items-center gap-3 mb-8">
          {["Date & Location", "Review & Pay"].map((label, i) => (
            <div key={i} className="flex items-center gap-2">
              <div className={cn(
                "w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold transition-colors",
                step > i + 1 ? "bg-green-500 text-white"
                  : step === i + 1 ? "bg-brand-500 text-white"
                  : "bg-dark-700 text-dark-400"
              )}>
                {step > i + 1 ? "✓" : i + 1}
              </div>
              <span className={cn("text-sm hidden sm:block", step === i + 1 ? "text-white font-medium" : "text-dark-500")}>
                {label}
              </span>
              {i < 1 && <div className={cn("h-px w-8 sm:w-16", step > 1 ? "bg-brand-500" : "bg-dark-700")} />}
            </div>
          ))}
        </div>

        <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
          {/* ── Form ── */}
          <div className="lg:col-span-2 space-y-6">
            {step === 1 && (
              <motion.div initial={{ opacity: 0, x: -20 }} animate={{ opacity: 1, x: 0 }} className="space-y-6">
                {/* Dates */}
                <div className="card p-6">
                  <h2 className="font-semibold text-white flex items-center gap-2 mb-4">
                    <CalendarDays className="w-5 h-5 text-brand-400" /> Rental Dates
                  </h2>
                  <div className="grid grid-cols-2 gap-4">
                    <div>
                      <label className="block text-xs text-dark-400 mb-1.5">Pick-up Date</label>
                      <input type="date" value={form.startDate} min={today}
                        onChange={(e) => update("startDate", e.target.value)}
                        className="input" />
                    </div>
                    <div>
                      <label className="block text-xs text-dark-400 mb-1.5">Return Date</label>
                      <input type="date" value={form.endDate} min={form.startDate}
                        onChange={(e) => update("endDate", e.target.value)}
                        className="input" />
                    </div>
                  </div>
                  {days > 0 && (
                    <p className="mt-3 text-sm text-brand-400 font-medium">
                      {days} day{days > 1 ? "s" : ""} — {formatCurrency(subtotal)} subtotal
                    </p>
                  )}
                </div>

                {/* Locations */}
                <div className="card p-6">
                  <h2 className="font-semibold text-white flex items-center gap-2 mb-4">
                    <MapPin className="w-5 h-5 text-brand-400" /> Locations
                  </h2>
                  <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                      <label className="block text-xs text-dark-400 mb-1.5">Pick-up Location</label>
                      <select value={form.pickupLocation} onChange={(e) => update("pickupLocation", e.target.value)} className="input">
                        {LOCATIONS.map((l) => <option key={l}>{l}</option>)}
                      </select>
                    </div>
                    <div>
                      <label className="block text-xs text-dark-400 mb-1.5">Drop-off Location</label>
                      <select value={form.dropoffLocation} onChange={(e) => update("dropoffLocation", e.target.value)} className="input">
                        {LOCATIONS.map((l) => <option key={l}>{l}</option>)}
                      </select>
                    </div>
                  </div>
                </div>

                {/* Driver Info */}
                <div className="card p-6">
                  <h2 className="font-semibold text-white flex items-center gap-2 mb-4">
                    <User className="w-5 h-5 text-brand-400" /> Driver Information
                  </h2>
                  <div className="grid grid-cols-2 gap-4">
                    <div>
                      <label className="block text-xs text-dark-400 mb-1.5">First Name</label>
                      <input type="text" value={form.firstName} onChange={(e) => update("firstName", e.target.value)} placeholder="John" className="input" />
                    </div>
                    <div>
                      <label className="block text-xs text-dark-400 mb-1.5">Last Name</label>
                      <input type="text" value={form.lastName} onChange={(e) => update("lastName", e.target.value)} placeholder="Doe" className="input" />
                    </div>
                    <div>
                      <label className="block text-xs text-dark-400 mb-1.5">Email</label>
                      <input type="email" value={form.email} onChange={(e) => update("email", e.target.value)} placeholder="john@example.com" className="input" />
                    </div>
                    <div>
                      <label className="block text-xs text-dark-400 mb-1.5">Phone</label>
                      <input type="tel" value={form.phone} onChange={(e) => update("phone", e.target.value)} placeholder="+1 (555) 000-0000" className="input" />
                    </div>
                  </div>
                  <div className="mt-4">
                    <label className="block text-xs text-dark-400 mb-1.5">Special Requests (optional)</label>
                    <textarea value={form.notes} onChange={(e) => update("notes", e.target.value)} rows={3} placeholder="Any special requests or notes..." className="input resize-none" />
                  </div>
                </div>

                <button onClick={handleStep1} className="btn-primary w-full justify-center py-4 text-base">
                  Continue to Review →
                </button>
              </motion.div>
            )}

            {step === 2 && (
              <motion.div initial={{ opacity: 0, x: 20 }} animate={{ opacity: 1, x: 0 }} className="space-y-6">
                {/* Booking Summary */}
                <div className="card p-6">
                  <h2 className="font-semibold text-white flex items-center gap-2 mb-4">
                    <AlertCircle className="w-5 h-5 text-brand-400" /> Booking Summary
                  </h2>
                  <div className="space-y-3 text-sm">
                    {[
                      { label: "Vehicle", value: `${car.make} ${car.model} (${car.year})` },
                      { label: "Dates", value: `${form.startDate} → ${form.endDate}` },
                      { label: "Duration", value: `${days} day${days > 1 ? "s" : ""}` },
                      { label: "Pick-up", value: form.pickupLocation },
                      { label: "Drop-off", value: form.dropoffLocation },
                      { label: "Driver", value: `${form.firstName} ${form.lastName}` },
                      { label: "Email", value: form.email },
                    ].map((row) => (
                      <div key={row.label} className="flex justify-between py-2 border-b border-dark-700/60 last:border-0">
                        <span className="text-dark-400">{row.label}</span>
                        <span className="text-white font-medium">{row.value}</span>
                      </div>
                    ))}
                  </div>
                </div>

                {/* Payment */}
                <div className="card p-6">
                  <h2 className="font-semibold text-white flex items-center gap-2 mb-4">
                    <CreditCard className="w-5 h-5 text-brand-400" /> Payment
                  </h2>
                  <div className="p-4 bg-dark-700/50 rounded-xl border border-dark-600 mb-4">
                    <p className="text-sm text-dark-400 mb-1">Card Number</p>
                    <p className="text-white font-mono">•••• •••• •••• 4242</p>
                    <p className="text-xs text-dark-500 mt-1">Demo mode — no real charge</p>
                  </div>
                  <div className="bg-brand-500/5 border border-brand-500/20 rounded-xl p-3 flex gap-2">
                    <AlertCircle className="w-4 h-4 text-brand-400 shrink-0 mt-0.5" />
                    <p className="text-xs text-brand-300">
                      This is a demo. In production, Stripe Checkout would handle secure payment.
                    </p>
                  </div>
                </div>

                {/* Terms */}
                <div className="card p-5">
                  <label className="flex items-start gap-3 cursor-pointer">
                    <div
                      onClick={() => update("agree", !form.agree)}
                      className={cn(
                        "w-5 h-5 rounded border-2 flex items-center justify-center shrink-0 mt-0.5 transition-colors",
                        form.agree ? "bg-brand-500 border-brand-500" : "border-dark-500 hover:border-brand-500"
                      )}
                    >
                      {form.agree && <svg className="w-3 h-3 text-white" fill="none" viewBox="0 0 12 12"><path d="M2 6l3 3 5-5" stroke="currentColor" strokeWidth={2} strokeLinecap="round" strokeLinejoin="round" /></svg>}
                    </div>
                    <span className="text-sm text-dark-300">
                      I agree to the{" "}
                      <Link href="#" className="text-brand-400 hover:underline">Terms of Service</Link> and{" "}
                      <Link href="#" className="text-brand-400 hover:underline">Rental Policy</Link>. I confirm I have a valid driver&apos;s license.
                    </span>
                  </label>
                </div>

                <button
                  onClick={handleConfirm}
                  disabled={loading || !form.agree}
                  className={cn(
                    "btn-primary w-full justify-center py-4 text-base",
                    (loading || !form.agree) && "opacity-60 cursor-not-allowed"
                  )}
                >
                  {loading ? (
                    <span className="flex items-center gap-2">
                      <span className="w-5 h-5 border-2 border-white/30 border-t-white rounded-full animate-spin" />
                      Processing...
                    </span>
                  ) : (
                    `Confirm & Pay ${formatCurrency(total)}`
                  )}
                </button>
              </motion.div>
            )}
          </div>

          {/* ── Order Summary Sidebar ── */}
          <div className="lg:col-span-1">
            <div className="sticky top-24 card overflow-hidden">
              <div className="relative h-44">
                <Image src={car.images[0]} alt={car.make} fill className="object-cover" />
                <div className="absolute inset-0 bg-gradient-to-t from-dark-900/80 to-transparent" />
                <div className="absolute bottom-3 left-3">
                  <p className="font-display font-bold text-white">{car.make} {car.model}</p>
                  <p className="text-dark-300 text-xs">{car.year}</p>
                </div>
              </div>

              <div className="p-5 space-y-3 text-sm">
                <div className="flex justify-between">
                  <span className="text-dark-400">{formatCurrency(car.pricePerDay)}/day × {days} days</span>
                  <span className="text-white">{formatCurrency(subtotal)}</span>
                </div>
                <div className="flex justify-between">
                  <span className="text-dark-400">Taxes & fees (10%)</span>
                  <span className="text-white">{formatCurrency(tax)}</span>
                </div>
                <div className="border-t border-dark-700 pt-3 flex justify-between font-bold text-base">
                  <span className="text-white">Total</span>
                  <span className="text-brand-400">{formatCurrency(total)}</span>
                </div>
                <p className="text-xs text-dark-500 text-center pt-1">Free cancellation · No hidden fees</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </AnimatedPage>
  );
}
