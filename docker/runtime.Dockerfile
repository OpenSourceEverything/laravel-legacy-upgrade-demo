ARG BASE_IMAGE
FROM ${BASE_IMAGE}
# Old PHP images use Debian's archive. This exception affects only this
# disposable historical runtime, never the host's package repositories.
RUN if grep -q stretch /etc/os-release; then \
      sed -i 's/deb.debian.org/archive.debian.org/g;s/security.debian.org/archive.debian.org/g;/stretch-updates/d' /etc/apt/sources.list; \
    fi \
    && apt-get -o Acquire::Check-Valid-Until=false update \
    && apt-get install -y --no-install-recommends unzip git \
    && rm -rf /var/lib/apt/lists/*
