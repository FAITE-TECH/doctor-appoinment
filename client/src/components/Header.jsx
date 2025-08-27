import React, { useState } from "react";

function Header() {
  const [openMenu, setOpenMenu] = useState(false);

  return (
    <>
      <div className="flex justify-between items-center px-6 py-4 bg-white shadow-md text-black relative">
        <div className="font-semibold text-lg">Logo</div>

        <div className="hidden md:block">
          <ul className="flex space-x-6 font-medium">
            <li className="cursor-pointer hover:text-blue-600">Home 🔽</li>
            <li className="cursor-pointer hover:text-blue-600">Doctor</li>
            <li className="cursor-pointer hover:text-blue-600">Department</li>
            <li className="cursor-pointer hover:text-blue-600">Services</li>
            <li className="cursor-pointer hover:text-blue-600">About</li>
            <li className="cursor-pointer hover:text-blue-600">Pages</li>
          </ul>
        </div>

        {/* Mobile Menu Button */}
        <button
          title="Toggle menu"
          type="button"
          className="md:hidden"
          onClick={() => setOpenMenu(!openMenu)}
        >
          {openMenu ? (
            <svg
              xmlns="http://www.w3.org/2000/svg"
              fill="none"
              viewBox="0 0 24 24"
              stroke="currentColor"
              className="w-7 h-7 text-red-800"
            >
              <path
                strokeLinecap="round"
                strokeLinejoin="round"
                strokeWidth="2"
                d="M6 18L18 6M6 6l12 12"
              />
            </svg>
          ) : (
            <svg
              xmlns="http://www.w3.org/2000/svg"
              fill="none"
              viewBox="0 0 24 24"
              stroke="currentColor"
              className="w-7 h-7 text-blue-800"
            >
              <path
                strokeLinecap="round"
                strokeLinejoin="round"
                strokeWidth="2"
                d="M4 6h16M4 12h16M4 18h16"
              />
            </svg>
          )}
        </button>

        <div
          className={`fixed top-0 right-0 h-full w-64 bg-white shadow-lg rounded-l-2xl md:hidden transform transition-transform duration-800 ease-in-out z-50 ${
            openMenu ? "translate-x-0" : "translate-x-full"
          }`}
        >
          {/* Close Button */}
          <div className="flex justify-end p-4">
            <button onClick={() => setOpenMenu(false)}>
              <svg
                xmlns="http://www.w3.org/2000/svg"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
                className="w-6 h-6 text-red-600"
              >
                <path
                  strokeLinecap="round"
                  strokeLinejoin="round"
                  strokeWidth="2"
                  d="M6 18L18 6M6 6l12 12"
                />
              </svg>
            </button>
          </div>

          {/* Nav Links */}
          <ul className="flex flex-col space-y-6 p-6 font-medium">
            <li className="cursor-pointer hover:text-blue-600">Home 🔽</li>
            <li className="cursor-pointer hover:text-blue-600">Doctor</li>
            <li className="cursor-pointer hover:text-blue-600">Department</li>
            <li className="cursor-pointer hover:text-blue-600">Services</li>
            <li className="cursor-pointer hover:text-blue-600">About</li>
            <li className="cursor-pointer hover:text-blue-600">Pages</li>
          </ul>
        </div>
      </div>
    </>
  );
}

export default Header;
