"use client";

import { useState } from "react";
import { useRouter } from "next/navigation";
import { motion } from "framer-motion";
import { ArrowLeft, Upload, Plus, X, Car } from "lucide-react";
import Link from "next/link";
import { AnimatedPage } from "@/components/AnimatedPage";
import { cn } from "@/lib/utils";
import toast from "react-hot-toast";

const CATEGORIES = ["SEDAN", "SUV", "SPORTS", "LUXURY", "ELECTRIC", "TRUCK", "VAN", "CONVERTIBLE"];
const FEATURES_POOL = [
  "GPS Navigation", "Heated Seats", "Sunroof", "Bluetooth", "Apple CarPlay",
  "Android Auto", "Backup Camera", "Lane Assist", "Wireless Charging",
  "Cruise Control", "Keyless Entry", "Air Suspension", "Sport Exhaust",
  "Premium Sound", "Ambient Lighting", "Head-Up Display",
];

const EMPTY_FORM = {
  make: "", model: "", year: new Date().getFullYear(), category: "SEDAN",
  pricePerDay: "", seats: 5, doors: 4, transmission: "AUTOMATIC",
  fuelType: "PETROL", color: "", location: "", description: "",
  licensePlate: "", features: [] as string[], isFeatured: false,
};

export default function AddCarPage() {
  const router = useRouter();
  const [form, setForm] = useState(EMPTY_FORM);
  const [images, setImages] = useState<string[]>([]);
  const [newImageUrl, setNewImageUrl] = useState("");
  const [saving, setSaving] = useState(false);

  const update = (field: string, value: unknown) =>
    setForm((prev) => ({ ...prev, [field]: value }));

  const toggleFeature = (feature: string) => {
    setForm((prev) => ({
      ...prev,
      features: prev.features.includes(feature)
        ? prev.features.filter((f) => f !== feature)
        : [...prev.features, feature],
    }));
  };

  const addImage = () => {
    if (!newImageUrl.trim()) return;
    setImages((prev) => [...prev, newImageUrl.trim()]);
    setNewImageUrl("");
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!form.make || !form.model || !form.pricePerDay)
      return toast.error("Please fill in all required fields");
    if (images.length === 0)
      return toast.error("Please add at least one image");

    setSaving(true);
    await new Promise((r) => setTimeout(r, 1500)); // Simulate API call
    setSaving(false);
    toast.success(`${form.make} ${form.model} added to fleet!`);
    router.push("/admin/cars");
  };

  return (
    <AnimatedPage className="min-h-screen pt-20">
      <div className="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {/* Header */}
        <div className="flex items-center gap-4 mb-8">
          <Link href="/admin/cars" className="p-2 rounded-lg text-dark-400 hover:text-white hover:bg-white/5 transition-colors">
            <ArrowLeft className="w-5 h-5" />
          </Link>
          <div>
            <p className="text-brand-400 text-sm font-semibold uppercase tracking-widest">Admin</p>
            <h1 className="font-display text-2xl font-bold text-white">Add New Car</h1>
          </div>
        </div>

        <form onSubmit={handleSubmit} className="space-y-6">
          {/* ── Basic Info ── */}
          <div className="card p-6">
            <h2 className="font-semibold text-white flex items-center gap-2 mb-5">
              <Car className="w-5 h-5 text-brand-400" /> Basic Information
            </h2>
            <div className="grid grid-cols-2 gap-4">
              <div>
                <label className="block text-xs text-dark-400 mb-1.5">Make *</label>
                <input value={form.make} onChange={(e) => update("make", e.target.value)} placeholder="e.g. BMW" className="input" required />
              </div>
              <div>
                <label className="block text-xs text-dark-400 mb-1.5">Model *</label>
                <input value={form.model} onChange={(e) => update("model", e.target.value)} placeholder="e.g. M5 Competition" className="input" required />
              </div>
              <div>
                <label className="block text-xs text-dark-400 mb-1.5">Year</label>
                <input type="number" value={form.year} onChange={(e) => update("year", parseInt(e.target.value))}
                  min={2000} max={2026} className="input" />
              </div>
              <div>
                <label className="block text-xs text-dark-400 mb-1.5">Price Per Day ($) *</label>
                <input type="number" value={form.pricePerDay} onChange={(e) => update("pricePerDay", e.target.value)}
                  placeholder="e.g. 299" min={1} className="input" required />
              </div>
              <div>
                <label className="block text-xs text-dark-400 mb-1.5">Color</label>
                <input value={form.color} onChange={(e) => update("color", e.target.value)} placeholder="e.g. Frozen Black" className="input" />
              </div>
              <div>
                <label className="block text-xs text-dark-400 mb-1.5">License Plate</label>
                <input value={form.licensePlate} onChange={(e) => update("licensePlate", e.target.value)} placeholder="e.g. ABC-1234" className="input" />
              </div>
              <div>
                <label className="block text-xs text-dark-400 mb-1.5">Location</label>
                <input value={form.location} onChange={(e) => update("location", e.target.value)} placeholder="e.g. Downtown Hub" className="input" />
              </div>
              <div>
                <label className="block text-xs text-dark-400 mb-1.5">Category</label>
                <select value={form.category} onChange={(e) => update("category", e.target.value)} className="input">
                  {CATEGORIES.map((c) => <option key={c} value={c}>{c.charAt(0) + c.slice(1).toLowerCase()}</option>)}
                </select>
              </div>
            </div>
          </div>

          {/* ── Specs ── */}
          <div className="card p-6">
            <h2 className="font-semibold text-white mb-5">Vehicle Specs</h2>
            <div className="grid grid-cols-2 sm:grid-cols-4 gap-4">
              <div>
                <label className="block text-xs text-dark-400 mb-1.5">Seats</label>
                <input type="number" value={form.seats} onChange={(e) => update("seats", parseInt(e.target.value))} min={1} max={12} className="input" />
              </div>
              <div>
                <label className="block text-xs text-dark-400 mb-1.5">Doors</label>
                <input type="number" value={form.doors} onChange={(e) => update("doors", parseInt(e.target.value))} min={2} max={6} className="input" />
              </div>
              <div>
                <label className="block text-xs text-dark-400 mb-1.5">Transmission</label>
                <select value={form.transmission} onChange={(e) => update("transmission", e.target.value)} className="input">
                  <option value="AUTOMATIC">Automatic</option>
                  <option value="MANUAL">Manual</option>
                </select>
              </div>
              <div>
                <label className="block text-xs text-dark-400 mb-1.5">Fuel Type</label>
                <select value={form.fuelType} onChange={(e) => update("fuelType", e.target.value)} className="input">
                  {["PETROL", "DIESEL", "ELECTRIC", "HYBRID"].map((f) => (
                    <option key={f} value={f}>{f.charAt(0) + f.slice(1).toLowerCase()}</option>
                  ))}
                </select>
              </div>
            </div>
          </div>

          {/* ── Description ── */}
          <div className="card p-6">
            <h2 className="font-semibold text-white mb-4">Description</h2>
            <textarea
              value={form.description}
              onChange={(e) => update("description", e.target.value)}
              rows={4}
              placeholder="Describe the vehicle, its features, and what makes it special..."
              className="input resize-none"
            />
          </div>

          {/* ── Images ── */}
          <div className="card p-6">
            <h2 className="font-semibold text-white flex items-center gap-2 mb-4">
              <Upload className="w-5 h-5 text-brand-400" /> Images
            </h2>
            <p className="text-xs text-dark-400 mb-4">Add image URLs (Unsplash, Cloudinary, etc.)</p>
            <div className="flex gap-2 mb-4">
              <input
                value={newImageUrl}
                onChange={(e) => setNewImageUrl(e.target.value)}
                onKeyDown={(e) => e.key === "Enter" && (e.preventDefault(), addImage())}
                placeholder="https://images.unsplash.com/..."
                className="input flex-1"
              />
              <button type="button" onClick={addImage} className="btn-secondary px-4 shrink-0">
                <Plus className="w-4 h-4" />
              </button>
            </div>
            {images.length > 0 && (
              <div className="flex flex-wrap gap-2">
                {images.map((url, i) => (
                  <div key={i} className="relative group">
                    <img src={url} alt={`img ${i}`} className="w-24 h-16 object-cover rounded-lg border border-dark-600" />
                    <button
                      type="button"
                      onClick={() => setImages((prev) => prev.filter((_, j) => j !== i))}
                      className="absolute -top-2 -right-2 w-5 h-5 rounded-full bg-red-500 text-white flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity"
                    >
                      <X className="w-3 h-3" />
                    </button>
                  </div>
                ))}
              </div>
            )}
          </div>

          {/* ── Features ── */}
          <div className="card p-6">
            <h2 className="font-semibold text-white mb-4">Features & Amenities</h2>
            <div className="flex flex-wrap gap-2">
              {FEATURES_POOL.map((feature) => (
                <button
                  type="button"
                  key={feature}
                  onClick={() => toggleFeature(feature)}
                  className={cn(
                    "px-3 py-1.5 rounded-lg text-xs font-medium transition-colors",
                    form.features.includes(feature)
                      ? "bg-brand-500 text-white"
                      : "bg-dark-700 text-dark-300 hover:text-white hover:bg-dark-600"
                  )}
                >
                  {form.features.includes(feature) && "✓ "}
                  {feature}
                </button>
              ))}
            </div>
          </div>

          {/* ── Featured toggle ── */}
          <div className="card p-5 flex items-center justify-between">
            <div>
              <p className="font-medium text-white">Mark as Featured</p>
              <p className="text-sm text-dark-400">Featured cars appear on the homepage</p>
            </div>
            <button
              type="button"
              onClick={() => update("isFeatured", !form.isFeatured)}
              className={cn("w-12 h-6 rounded-full transition-colors relative", form.isFeatured ? "bg-brand-500" : "bg-dark-600")}
            >
              <span className={cn("absolute top-1 w-4 h-4 bg-white rounded-full shadow transition-transform", form.isFeatured ? "translate-x-7" : "translate-x-1")} />
            </button>
          </div>

          {/* Submit */}
          <button
            type="submit"
            disabled={saving}
            className={cn("btn-primary w-full justify-center py-4 text-base", saving && "opacity-60 cursor-not-allowed")}
          >
            {saving ? (
              <span className="flex items-center gap-2">
                <span className="w-5 h-5 border-2 border-white/30 border-t-white rounded-full animate-spin" />
                Saving...
              </span>
            ) : (
              "Add Car to Fleet"
            )}
          </button>
        </form>
      </div>
    </AnimatedPage>
  );
}
