import Link from "next/link";
import { Car, Instagram, Twitter, Facebook, Mail, Phone, MapPin } from "lucide-react";

export function Footer() {
  return (
    <footer className="bg-dark-900 border-t border-dark-800 mt-20">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-14">
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-10">
          {/* Brand */}
          <div className="space-y-4">
            <Link href="/" className="flex items-center gap-2">
              <div className="w-8 h-8 bg-brand-500 rounded-lg flex items-center justify-center">
                <Car className="w-5 h-5 text-white" />
              </div>
              <span className="font-display text-xl font-bold text-white">
                Drive<span className="text-brand-500">Elite</span>
              </span>
            </Link>
            <p className="text-dark-400 text-sm leading-relaxed">
              Premium car rentals for every occasion. Drive the car of your dreams with transparent pricing
              and exceptional service.
            </p>
            <div className="flex gap-3">
              {[Instagram, Twitter, Facebook].map((Icon, i) => (
                <button
                  key={i}
                  className="w-9 h-9 rounded-lg bg-dark-800 border border-dark-700 flex items-center justify-center text-dark-400 hover:text-brand-400 hover:border-brand-500/40 transition-colors"
                >
                  <Icon className="w-4 h-4" />
                </button>
              ))}
            </div>
          </div>

          {/* Quick Links */}
          <div>
            <h4 className="font-semibold text-white mb-4">Quick Links</h4>
            <ul className="space-y-2.5">
              {[
                { href: "/cars", label: "Browse Cars" },
                { href: "/cars?category=LUXURY", label: "Luxury Fleet" },
                { href: "/cars?category=ELECTRIC", label: "Electric Cars" },
                { href: "/account/bookings", label: "My Bookings" },
                { href: "/account", label: "My Account" },
              ].map((link) => (
                <li key={link.href}>
                  <Link
                    href={link.href}
                    className="text-sm text-dark-400 hover:text-brand-400 transition-colors"
                  >
                    {link.label}
                  </Link>
                </li>
              ))}
            </ul>
          </div>

          {/* Support */}
          <div>
            <h4 className="font-semibold text-white mb-4">Support</h4>
            <ul className="space-y-2.5">
              {[
                { href: "#", label: "How It Works" },
                { href: "#", label: "FAQs" },
                { href: "#", label: "Insurance & Protection" },
                { href: "#", label: "Cancellation Policy" },
                { href: "#", label: "Terms of Service" },
                { href: "#", label: "Privacy Policy" },
              ].map((link) => (
                <li key={link.label}>
                  <Link
                    href={link.href}
                    className="text-sm text-dark-400 hover:text-brand-400 transition-colors"
                  >
                    {link.label}
                  </Link>
                </li>
              ))}
            </ul>
          </div>

          {/* Contact */}
          <div>
            <h4 className="font-semibold text-white mb-4">Contact Us</h4>
            <ul className="space-y-3">
              <li className="flex items-start gap-3">
                <MapPin className="w-4 h-4 text-brand-400 mt-0.5 shrink-0" />
                <span className="text-sm text-dark-400">123 Elite Drive, Beverly Hills, CA 90210</span>
              </li>
              <li className="flex items-center gap-3">
                <Phone className="w-4 h-4 text-brand-400 shrink-0" />
                <a href="tel:+18005550100" className="text-sm text-dark-400 hover:text-brand-400 transition-colors">
                  +1 (800) 555-0100
                </a>
              </li>
              <li className="flex items-center gap-3">
                <Mail className="w-4 h-4 text-brand-400 shrink-0" />
                <a href="mailto:hello@driveelite.com" className="text-sm text-dark-400 hover:text-brand-400 transition-colors">
                  hello@driveelite.com
                </a>
              </li>
            </ul>
            <div className="mt-5 p-3 bg-brand-500/10 border border-brand-500/20 rounded-xl">
              <p className="text-xs text-brand-300 font-medium">24/7 Support Available</p>
              <p className="text-xs text-dark-400 mt-0.5">We&apos;re here whenever you need us</p>
            </div>
          </div>
        </div>

        <div className="mt-12 pt-6 border-t border-dark-800 flex flex-col sm:flex-row items-center justify-between gap-4">
          <p className="text-sm text-dark-500">
            © {new Date().getFullYear()} DriveElite. All rights reserved.
          </p>
          <div className="flex items-center gap-4">
            <img src="https://via.placeholder.com/40x25/1e293b/ffffff?text=VISA" alt="Visa" className="h-6 rounded opacity-60" />
            <img src="https://via.placeholder.com/40x25/1e293b/ffffff?text=MC" alt="Mastercard" className="h-6 rounded opacity-60" />
            <img src="https://via.placeholder.com/40x25/1e293b/ffffff?text=AMEX" alt="Amex" className="h-6 rounded opacity-60" />
            <span className="text-xs text-dark-500">Powered by Stripe</span>
          </div>
        </div>
      </div>
    </footer>
  );
}
